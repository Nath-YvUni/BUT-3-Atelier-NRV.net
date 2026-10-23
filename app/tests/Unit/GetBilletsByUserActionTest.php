<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\GetBilletsByUserAction;
use nrv\application\ports\api\ServiceBilletInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

function appelerBilletsUtilisateur(
    GetBilletsByUserAction $action,
    ?array $utilisateur
) {
    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/billets')
        ->withAttribute('utilisateur', $utilisateur);

    return $action(
        $requete,
        (new ResponseFactory())->createResponse(),
        []
    );
}

beforeEach(function () {
    $this->service = $this->createMock(ServiceBilletInterface::class);
    $this->action = new GetBilletsByUserAction($this->service);
});

test('répond 200 avec les billets de l utilisateur', function () {
    $billets = [
        [
            'reference' => 'BIL-2026-0001',
            'token' => str_repeat('ab', 32),
            'acheteur' => 'Alice Martin'
        ],
        [
            'reference' => 'BIL-2026-0002',
            'token' => str_repeat('cd', 32),
            'acheteur' => 'Alice Martin'
        ]
    ];

    $this->service->expects($this->once())
        ->method('getUserBillets')
        ->with(2)
        ->willReturn($billets);

    $reponse = appelerBilletsUtilisateur(
        $this->action,
        ['id' => 2]
    );

    $corps = json_decode((string) $reponse->getBody(), true);

    expect($reponse->getStatusCode())->toBe(200);
    expect($corps['data'])->toBe($billets);
});

test('répond 200 avec une liste vide si l utilisateur ne possède aucun billet', function () {
    $this->service->expects($this->once())
        ->method('getUserBillets')
        ->with(4)
        ->willReturn([]);

    $reponse = appelerBilletsUtilisateur(
        $this->action,
        ['id' => 4]
    );

    $corps = json_decode((string) $reponse->getBody(), true);

    expect($reponse->getStatusCode())->toBe(200);
    expect($corps['data'])->toBe([]);
});

test('répond 401 si l utilisateur n est pas authentifié', function () {
    $this->service->expects($this->never())
        ->method('getUserBillets');

    $reponse = appelerBilletsUtilisateur(
        $this->action,
        null
    );

    expect($reponse->getStatusCode())->toBe(401);
});
