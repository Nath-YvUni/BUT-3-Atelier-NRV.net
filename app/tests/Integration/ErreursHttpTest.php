<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\GetDetailsSoireeAction;
use nrv\adapters\controllers\middlewares\ApiErrorRenderer;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\ports\api\ServiceSoireeInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

beforeEach(function () {
    $this->appErreurs = AppFactory::create();
    $this->appErreurs->addRoutingMiddleware();

    $middleware = $this->appErreurs->addErrorMiddleware(
        true,
        false,
        false
    );

    $handler = $middleware->getDefaultErrorHandler();
    $handler->forceContentType('application/json');
    $handler->registerErrorRenderer(
        'application/json',
        new ApiErrorRenderer()
    );
});

test('une route inconnue renvoie une erreur JSON 404', function () {
    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/route-inconnue');

    $reponse = $this->appErreurs->handle($requete);

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(404);

    expect($reponse->getHeaderLine('Content-Type'))
        ->toContain('application/json');

    expect($contenu['error']['code'])->toBe('NOT_FOUND');
    expect($contenu['error']['message'])->toBeString();
    expect($contenu['error']['details'])->toBe([]);
    expect($contenu)->not->toHaveKey('message');
});

test('une soirée absente renvoie une erreur JSON 404', function () {
    $service = $this->createMock(ServiceSoireeInterface::class);

    $service->expects($this->once())
        ->method('getDetails')
        ->with(999)
        ->willThrowException(new SoireeIntrouvableException());

    $this->appErreurs->get(
        '/api/soirees/{id}',
        new GetDetailsSoireeAction($service)
    );

    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/soirees/999');

    $reponse = $this->appErreurs->handle($requete);

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(404);

    expect($contenu)->toBe([
        'error' => [
            'code' => 'NOT_FOUND',
            'message' => 'Cette soirée n’existe pas.',
            'details' => [],
        ],
    ]);
});

test('une erreur interne renvoie un message générique', function () {
    $this->appErreurs->get('/erreur', static function () {
        throw new RuntimeException('Détail technique à masquer.');
    });

    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/erreur');

    $reponse = $this->appErreurs->handle($requete);

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(500);

    expect($contenu)->toBe([
        'error' => [
            'code' => 'INTERNAL_SERVER_ERROR',
            'message' => 'Une erreur interne est survenue.',
            'details' => [],
        ],
    ]);
});

test('une méthode interdite conserve le statut 405 et le header Allow', function () {
    $this->appErreurs->get(
        '/ressource',
        static fn($requete, $reponse) => $reponse
    );

    $requete = (new ServerRequestFactory())
        ->createServerRequest('POST', '/ressource');

    $reponse = $this->appErreurs->handle($requete);

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(405);

    expect($reponse->getHeaderLine('Allow'))
        ->toContain('GET');

    expect($contenu['error']['code'])
        ->toBe('METHOD_NOT_ALLOWED');
});