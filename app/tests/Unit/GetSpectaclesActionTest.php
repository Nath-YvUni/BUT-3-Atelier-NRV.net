<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\GetSpectaclesAction;
use nrv\application\ports\api\ServiceSpectacleInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

test('renvoie une liste vide en JSON avec le statut 200', function () {
    $service = $this->createMock(ServiceSpectacleInterface::class);

    $service->expects($this->once())
        ->method('getSpectacles')
        ->willReturn([]);

    $action = new GetSpectaclesAction($service);

    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/spectacles');

    $reponse = $action(
        $requete,
        (new ResponseFactory())->createResponse(),
        []
    );

    expect($reponse->getStatusCode())->toBe(200);

    expect($reponse->getHeaderLine('Content-Type'))
        ->toContain('application/json');

    expect(
        json_decode(
            (string) $reponse->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        )
    )->toBe([]);
});

test('renvoie les spectacles fournis par le service en JSON', function () {
    $spectacles = [
        [
            'id' => 1,
            'soiree_id' => 1,
            'titre' => 'Pistons & Chrome',
            'date' => '2027-06-04',
            'horaire' => '20:00',
            'image' => 'https://example.com/pistons.jpg',
        ],
    ];

    $service = $this->createMock(ServiceSpectacleInterface::class);

    $service->expects($this->once())
        ->method('getSpectacles')
        ->willReturn($spectacles);

    $action = new GetSpectaclesAction($service);

    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/spectacles');

    $reponse = $action(
        $requete,
        (new ResponseFactory())->createResponse(),
        []
    );

    expect($reponse->getStatusCode())->toBe(200);

    expect($reponse->getHeaderLine('Content-Type'))
        ->toContain('application/json');

    expect(
        json_decode(
            (string) $reponse->getBody(),
            true,
            512,
            JSON_THROW_ON_ERROR
        )
    )->toBe($spectacles);
});

test('transmet la date et style_id au service', function () {
    $service = $this->createMock(ServiceSpectacleInterface::class);

    $service->expects($this->once())
        ->method('getSpectacles')
        ->with('2027-06-04', '1')
        ->willReturn([]);

    $action = new GetSpectaclesAction($service);

    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/spectacles')
        ->withQueryParams([
            'date' => '2027-06-04',
            'style_id' => '1',
        ]);

    $reponse = $action(
        $requete,
        (new ResponseFactory())->createResponse(),
        []
    );

    expect($reponse->getStatusCode())->toBe(200);
});

test('refuse les paramètres de style mal formés', function (array $parametres, string $champ) {
    $service = $this->createMock(ServiceSpectacleInterface::class);

    $service->expects($this->never())
        ->method('getSpectacles');

    $action = new GetSpectaclesAction($service);

    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/spectacles')
        ->withQueryParams($parametres);

    $reponse = $action(
        $requete,
        (new ResponseFactory())->createResponse(),
        []
    );

    $contenu = json_decode(
        (string) $reponse->getBody(),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($reponse->getStatusCode())->toBe(422);
    expect($contenu['error']['code'])->toBe('VALIDATION_ERROR');
    expect($contenu['error']['details'][0]['champ'])->toBe($champ);
})->with([
            'plusieurs identifiants' => [
                ['style_id' => ['1', '7']],
                'style_id',
            ],
            'ancien paramètre' => [
                ['style' => 'Classic rock'],
                'style',
            ],
        ]);