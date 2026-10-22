<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\ConnexionAction;
use nrv\adapters\controllers\actions\InscriptionAction;
use nrv\adapters\controllers\actions\ProfilAction;
use nrv\application\exceptions\AuthentificationException;
use nrv\application\exceptions\EmailDejaUtiliseException;
use nrv\application\exceptions\ValidationException;
use nrv\application\ports\api\ServiceAuthInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

beforeEach(function () {
    $this->serviceAuth = $this->createMock(
        ServiceAuthInterface::class
    );

    $this->requete = (new ServerRequestFactory())
        ->createServerRequest('POST', '/api/utilisateurs')
        ->withHeader('Content-Type', 'application/json');

    $this->reponse = (new ResponseFactory())->createResponse();
});

test('inscription réussie : renvoie le profil avec le statut 201', function () {
    $donnees = [
        'nom' => 'Alice Martin',
        'email' => 'alice@example.com',
        'mot_de_passe' => 'UnePhraseDePassePourNRV!',
    ];

    $profil = [
        'id' => 5,
        'nom' => 'Alice Martin',
        'email' => 'alice@example.com',
        'role' => 'utilisateur',
    ];

    $this->serviceAuth->expects($this->once())
        ->method('inscrire')
        ->with($donnees)
        ->willReturn($profil);

    $this->requete->getBody()->write(
        json_encode($donnees, JSON_THROW_ON_ERROR)
    );

    $action = new InscriptionAction($this->serviceAuth);

    $reponse = $action($this->requete, $this->reponse, []);

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(201);

    expect($reponse->getHeaderLine('Content-Type'))
        ->toContain('application/json');

    expect($contenu['data'])->toBe($profil);

    expect($contenu['_links']['connexion']['href'])
        ->toBe('/api/auth/connexion');
});

test('connexion réussie : renvoie le jeton avec le statut 200', function () {
    $donnees = [
        'email' => 'alice@example.com',
        'mot_de_passe' => 'password',
    ];

    $resultat = [
        'access_token' => 'jeton-de-test',
        'token_type' => 'Bearer',
        'expires_in' => 3600,
        'utilisateur' => [
            'id' => 2,
            'nom' => 'Alice Martin',
            'email' => 'alice@example.com',
            'role' => 'utilisateur',
        ],
    ];

    $this->serviceAuth->expects($this->once())
        ->method('connecter')
        ->with($donnees)
        ->willReturn($resultat);

    $requete = (new ServerRequestFactory())
        ->createServerRequest('POST', '/api/auth/connexion')
        ->withHeader('Content-Type', 'application/json');

    $requete->getBody()->write(
        json_encode($donnees, JSON_THROW_ON_ERROR)
    );

    $action = new ConnexionAction($this->serviceAuth);

    $reponse = $action($requete, $this->reponse, []);

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(200);
    expect($contenu['data'])->toBe($resultat);
});

test('adresse déjà utilisée : renvoie 409', function () {
    $this->serviceAuth->method('inscrire')
        ->willThrowException(new EmailDejaUtiliseException());

    $this->requete->getBody()->write('{}');

    $action = new InscriptionAction($this->serviceAuth);

    $reponse = $action($this->requete, $this->reponse, []);

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(409);

    expect($contenu['error']['code'])
        ->toBe('EMAIL_DEJA_UTILISE');
});

test('données invalides : renvoie 422 avec le champ concerné', function () {
    $this->serviceAuth->method('inscrire')
        ->willThrowException(new ValidationException(
            ['email' => 'Adresse invalide.'],
            'Données invalides.'
        ));

    $this->requete->getBody()->write('{}');

    $action = new InscriptionAction($this->serviceAuth);

    $reponse = $action($this->requete, $this->reponse, []);

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(422);

    expect($contenu['error']['details'])->toBe([
        [
            'champ' => 'email',
            'message' => 'Adresse invalide.',
        ],
    ]);
});

test('identifiants incorrects : renvoie 401', function () {
    $this->serviceAuth->method('connecter')
        ->willThrowException(new AuthentificationException(
            'Adresse e-mail ou mot de passe incorrect.'
        ));

    $this->requete->getBody()->write('{}');

    $action = new ConnexionAction($this->serviceAuth);

    $reponse = $action($this->requete, $this->reponse, []);

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(401);

    expect($contenu['error']['code'])
        ->toBe('AUTHENTIFICATION_ECHOUEE');
});

test('corps JSON incorrect : renvoie 400 sans appeler le service', function (string $corps) {
    $this->serviceAuth->expects($this->never())
        ->method('inscrire');

    $this->requete->getBody()->write($corps);

    $action = new InscriptionAction($this->serviceAuth);

    $reponse = $action($this->requete, $this->reponse, []);

    expect($reponse->getStatusCode())->toBe(400);
})->with([
            'JSON mal formé' => ['{'],
            'tableau au lieu d’un objet' => ['[]'],
            'valeur null' => ['null'],
        ]);

test('format non accepté : renvoie 415 sans appeler le service', function () {
    $this->serviceAuth->expects($this->never())
        ->method('inscrire');

    $requete = $this->requete
        ->withHeader('Content-Type', 'text/plain');

    $requete->getBody()->write('{}');

    $action = new InscriptionAction($this->serviceAuth);

    $reponse = $action($requete, $this->reponse, []);

    expect($reponse->getStatusCode())->toBe(415);
});

test('profil connecté : renvoie le profil fourni par le middleware', function () {
    $profil = [
        'id' => 2,
        'nom' => 'Alice Martin',
        'email' => 'alice@example.com',
        'role' => 'utilisateur',
    ];

    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/me')
        ->withAttribute('utilisateur', $profil);

    $action = new ProfilAction();

    $reponse = $action($requete, $this->reponse, []);

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(200);
    expect($contenu['data'])->toBe($profil);
});

test('profil absent : renvoie 401', function () {
    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/me');

    $action = new ProfilAction();

    $reponse = $action($requete, $this->reponse, []);

    expect($reponse->getStatusCode())->toBe(401);
});