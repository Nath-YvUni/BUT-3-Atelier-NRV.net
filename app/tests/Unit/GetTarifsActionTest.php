<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\GetTarifsAction;
use nrv\application\ports\api\ServiceTarifInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

test('renvoie les tarifs dans data avec le statut 200', function () {
    $tarifs = [
        ['id' => 1, 'categorie' => 'normal', 'prix' => '35.00'],
        ['id' => 2, 'categorie' => 'reduit', 'prix' => '25.00'],
    ];

    $service = $this->createMock(ServiceTarifInterface::class);

    $service->expects($this->once())
        ->method('getTarifs')
        ->willReturn($tarifs);

    $reponse = (new GetTarifsAction($service))(
        (new ServerRequestFactory())->createServerRequest('GET', '/api/tarifs'),
        (new ResponseFactory())->createResponse(),
        []
    );

    expect($reponse->getStatusCode())->toBe(200);
    expect($reponse->getHeaderLine('Content-Type'))->toContain('application/json');
    expect(json_decode((string) $reponse->getBody(), true))->toBe(['data' => $tarifs]);
});
