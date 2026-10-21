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