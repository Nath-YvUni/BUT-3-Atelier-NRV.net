<?php
declare(strict_types=1);

use nrv\application\exceptions\AuthentificationException;
use nrv\application\exceptions\EmailDejaUtiliseException;
use nrv\application\exceptions\ValidationException;
use nrv\application\ports\spi\GestionJetonInterface;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use nrv\application\usecases\ServiceAuth;
use nrv\domain\entities\Utilisateur;

beforeEach(function () {
    $this->utilisateurs = $this->createMock(
        UtilisateurRepositoryInterface::class
    );

    $this->jetons = $this->createMock(
        GestionJetonInterface::class
    );

    $this->service = new ServiceAuth(
        $this->utilisateurs,
        $this->jetons
    );
});

test('inscrit un utilisateur avec un mot de passe haché', function () {
    $motDePasse = 'UnMotDePassePourNRV!';

    $this->utilisateurs->method('trouverParEmail')
        ->willReturn(null);

    $this->utilisateurs->expects($this->once())
        ->method('creer')
        ->willReturnCallback(
            function (string $nom, string $email, string $hash) use ($motDePasse) {
                expect($nom)->toBe('Alice Martin');
                expect($email)->toBe('alice@example.com');
                expect($hash)->not->toBe($motDePasse);
                expect(password_verify($motDePasse, $hash))
                    ->toBeTrue();

                return new Utilisateur(
                    5,
                    $nom,
                    $email,
                    $hash,
                    'utilisateur'
                );
            }
        );

    $resultat = $this->service->inscrire([
        'nom' => ' Alice Martin ',
        'email' => ' ALICE@example.com ',
        'mot_de_passe' => $motDePasse,
    ]);

    expect($resultat)->toBe([
        'id' => 5,
        'nom' => 'Alice Martin',
        'email' => 'alice@example.com',
        'role' => 'utilisateur',
    ]);
});

test('refuse une inscription invalide avant de créer le compte', function (array $donnees) {
    $this->utilisateurs->expects($this->never())->method('creer');

    expect(fn() => $this->service->inscrire($donnees))->toThrow(ValidationException::class);
})->with([
            'champs invalides' => [
                [
                    'nom' => '',
                    'email' => 'adresse-invalide',
                    'mot_de_passe' => 'court',
                ]
            ],
            'rôle interdit' => [
                [
                    'nom' => 'Alice Martin',
                    'email' => 'alice@example.com',
                    'mot_de_passe' => 'UnMotDePassePourNRV!',
                    'role' => 'gestionnaire',
                ]
            ],
        ]);

test('refuse une adresse e-mail déjà utilisée', function () {
    $this->utilisateurs->method('trouverParEmail')
        ->willReturn(new Utilisateur(
            2,
            'Alice Martin',
            'alice@example.com',
            password_hash('password', PASSWORD_DEFAULT),
            'utilisateur'
        ));

    $this->utilisateurs->expects($this->never())->method('creer');

    expect(fn() => $this->service->inscrire([
        'nom' => 'Alice Martin',
        'email' => 'alice@example.com',
        'mot_de_passe' => 'UnMotDePassePourNRV!',
    ]))->toThrow(EmailDejaUtiliseException::class);
});

test('connecte un compte existant et renvoie son jeton', function () {
    $this->utilisateurs->method('trouverParEmail')
        ->willReturn(new Utilisateur(
            2,
            'Alice Martin',
            'alice@example.com',
            password_hash('password', PASSWORD_DEFAULT),
            'utilisateur'
        ));

    $this->jetons->expects($this->once())
        ->method('creer')
        ->with(2)
        ->willReturn([
            'access_token' => 'jeton-de-test',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);

    $resultat = $this->service->connecter([
        'email' => 'alice@example.com',
        'mot_de_passe' => 'password',
    ]);

    expect($resultat)->toBe([
        'access_token' => 'jeton-de-test',
        'token_type' => 'Bearer',
        'expires_in' => 3600,
        'utilisateur' => [
            'id' => 2,
            'nom' => 'Alice Martin',
            'email' => 'alice@example.com',
            'role' => 'utilisateur',
        ],
    ]);
});

test('refuse la connexion sans révéler si le compte existe', function (bool $compteExiste) {
    $utilisateur = $compteExiste
        ? new Utilisateur(
            2,
            'Alice Martin',
            'alice@example.com',
            password_hash('password', PASSWORD_DEFAULT),
            'utilisateur'
        )
        : null;

    $this->utilisateurs->method('trouverParEmail')->willReturn($utilisateur);

    $this->jetons->expects($this->never())->method('creer');

    expect(fn() => $this->service->connecter([
        'email' => 'alice@example.com',
        'mot_de_passe' => 'mauvais-mot-de-passe',
    ]))->toThrow(AuthentificationException::class, 'Adresse e-mail ou mot de passe incorrect.');
})->with([
            'compte inconnu' => [false],
            'mauvais mot de passe' => [true],
        ]);

test('refuse le profil d’un utilisateur supprimé', function () {
    $this->utilisateurs->method('trouverParId')
        ->with(99)
        ->willReturn(null);

    expect(fn() => $this->service->profil(99))->toThrow(AuthentificationException::class);
});