<?php
declare(strict_types=1);

use nrv\application\exceptions\AuthentificationException;
use nrv\application\ports\api\ServiceAuthInterface;
use nrv\application\ports\api\ServiceStatistiqueInterface;
use nrv\application\ports\spi\GestionJetonInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

beforeEach(function () {
    $this->jetons = $this->createMock(
        GestionJetonInterface::class
    );

    $this->serviceAuth = $this->createMock(
        ServiceAuthInterface::class
    );

    $this->serviceStatistiques = $this->createMock(
        ServiceStatistiqueInterface::class
    );

    $this->appStatistiques = require dirname(__DIR__, 2)
        . '/config/bootstrap.php';

    $container = $this->appStatistiques->getContainer();

    $container->set(
        GestionJetonInterface::class,
        $this->jetons
    );

    $container->set(
        ServiceAuthInterface::class,
        $this->serviceAuth
    );

    $container->set(
        ServiceStatistiqueInterface::class,
        $this->serviceStatistiques
    );

    $this->requeteStatistiques = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/admin/statistiques');
});

test('sans jeton : refuse les statistiques avec le statut 401', function () {
    $this->jetons->expects($this->never())
        ->method('lire');

    $this->serviceAuth->expects($this->never())
        ->method('profil');

    $this->serviceStatistiques->expects($this->never())
        ->method('getStatistiques');

    $reponse = $this->appStatistiques->handle(
        $this->requeteStatistiques
    );

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(401);

    expect($reponse->getHeaderLine('WWW-Authenticate'))
        ->toContain('Bearer');

    expect($contenu['error']['code'])
        ->toBe('AUTHENTIFICATION_REQUISE');
});

test('jeton invalide : refuse les statistiques avec le statut 401', function () {
    $this->jetons->expects($this->once())
        ->method('lire')
        ->with('jeton-invalide')
        ->willThrowException(
            new AuthentificationException('Jeton invalide.')
        );

    $this->serviceAuth->expects($this->never())
        ->method('profil');

    $this->serviceStatistiques->expects($this->never())
        ->method('getStatistiques');

    $requete = $this->requeteStatistiques->withHeader(
        'Authorization',
        'Bearer jeton-invalide'
    );

    $reponse = $this->appStatistiques->handle($requete);

    expect($reponse->getStatusCode())->toBe(401);
});

test('utilisateur connecté : refuse les statistiques avec le statut 403', function () {
    $this->jetons->expects($this->once())
        ->method('lire')
        ->with('jeton-utilisateur')
        ->willReturn(2);

    $this->serviceAuth->expects($this->once())
        ->method('profil')
        ->with(2)
        ->willReturn([
            'id' => 2,
            'nom' => 'Alice Martin',
            'email' => 'alice@example.com',
            'role' => 'utilisateur',
        ]);

    $this->serviceStatistiques->expects($this->never())
        ->method('getStatistiques');

    $requete = $this->requeteStatistiques
        ->withHeader('Authorization', 'Bearer jeton-utilisateur')
        ->withQueryParams(['role' => 'gestionnaire']);

    $reponse = $this->appStatistiques->handle($requete);

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(403);

    expect($contenu)->toBe([
        'error' => [
            'code' => 'ACCES_INTERDIT',
            'message' => 'Accès réservé aux gestionnaires.',
            'details' => [],
        ],
    ]);
});

test('gestionnaire connecté : renvoie les statistiques avec le statut 200', function () {
    $statistiques = [
        'soirees' => [
            [
                'id' => 7,
                'nom' => 'Rock',
                'capacite' => 3,
                'places_vendues' => 3,
                'places_disponibles' => 0,
                'recette' => '94.95',
            ],
        ],
        'total_billets_vendus' => 3,
        'recette_totale' => '94.95',
        'devise' => 'EUR',
    ];

    $this->jetons->expects($this->once())
        ->method('lire')
        ->with('jeton-gestionnaire')
        ->willReturn(1);

    $this->serviceAuth->expects($this->once())
        ->method('profil')
        ->with(1)
        ->willReturn([
            'id' => 1,
            'nom' => 'Gestionnaire NRV',
            'email' => 'gestionnaire@nrv.net',
            'role' => 'gestionnaire',
        ]);

    $this->serviceStatistiques->expects($this->once())
        ->method('getStatistiques')
        ->willReturn($statistiques);

    $requete = $this->requeteStatistiques->withHeader(
        'Authorization',
        'Bearer jeton-gestionnaire'
    );

    $reponse = $this->appStatistiques->handle($requete);

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(200);

    expect($reponse->getHeaderLine('Content-Type'))
        ->toContain('application/json');

    expect($contenu)->toBe([
        'data' => $statistiques,
    ]);
});