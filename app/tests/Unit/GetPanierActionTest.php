<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\GetPanierAction;
use nrv\application\ports\api\ServicePanierInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

function appelerConsultationPanier(
    GetPanierAction $action,
    ?array $utilisateur = ['id' => 3, 'role' => 'utilisateur']
): array {
    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/panier')
        ->withAttribute('utilisateur', $utilisateur);

    $reponse = $action($requete, (new ResponseFactory())->createResponse(), []);

    return [
        $reponse->getStatusCode(),
        json_decode((string) $reponse->getBody(), true),
    ];
}

beforeEach(function () {
    $this->service = $this->createMock(ServicePanierInterface::class);
    $this->action = new GetPanierAction($this->service);
});

test('répond 200 avec le panier de l\'utilisateur authentifié', function () {
    $panier = ['id' => 1, 'lignes' => [], 'montant_total' => '70.00'];

    $this->service->expects($this->once())
        ->method('consulterPanier')
        ->with(3)
        ->willReturn($panier);

    [$statut, $corps] = appelerConsultationPanier($this->action);

    expect($statut)->toBe(200);
    expect($corps['data'])->toBe($panier);
});

test('répond 401 sans utilisateur authentifié', function () {
    $this->service->expects($this->never())->method('consulterPanier');

    [$statut, $corps] = appelerConsultationPanier($this->action, null);

    expect($statut)->toBe(401);
    expect($corps['error']['code'])->toBe('AUTHENTIFICATION_REQUISE');
});
