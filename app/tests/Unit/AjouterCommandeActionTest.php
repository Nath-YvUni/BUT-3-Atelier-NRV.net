<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\AjouterCommandeAction;
use nrv\application\exceptions\CommandeEnAttenteException;
use nrv\application\exceptions\PanierVideException;
use nrv\application\ports\api\ServiceCommandeInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

function appelerCreationCommande(
    AjouterCommandeAction $action,
    ?array $utilisateur = ['id' => 3, 'role' => 'utilisateur']
): array {
    $requete = (new ServerRequestFactory())
        ->createServerRequest('POST', '/api/commandes')
        ->withAttribute('utilisateur', $utilisateur);

    $reponse = $action($requete, (new ResponseFactory())->createResponse(), []);

    return [
        $reponse->getStatusCode(),
        json_decode((string) $reponse->getBody(), true),
    ];
}

beforeEach(function () {
    $this->service = $this->createMock(ServiceCommandeInterface::class);
    $this->action = new AjouterCommandeAction($this->service);
});

test('répond 201 avec la commande et les liens vers son détail et son paiement', function () {
    $commande = ['id' => 3, 'reference' => 'CMD-2026-0003', 'montant_total' => '167.75'];

    $this->service->expects($this->once())
        ->method('creerCommande')
        ->with(3, [])
        ->willReturn($commande);

    [$statut, $corps] = appelerCreationCommande($this->action);

    expect($statut)->toBe(201);
    expect($corps['data'])->toBe($commande);
    expect($corps['_links']['self']['href'])->toBe('/api/commandes/3');
    expect($corps['_links']['paiement'])->toBe([
        'href' => '/api/commandes/3/paiement',
        'method' => 'POST',
    ]);
});

test('répond 422 quand le panier est vide', function () {
    $this->service->method('creerCommande')->willThrowException(new PanierVideException());

    [$statut, $corps] = appelerCreationCommande($this->action);

    expect($statut)->toBe(422);
    expect($corps['error']['code'])->toBe('PANIER_VIDE');
});

test('répond 409 quand une commande est déjà en attente', function () {
    $this->service->method('creerCommande')->willThrowException(new CommandeEnAttenteException());

    [$statut, $corps] = appelerCreationCommande($this->action);

    expect($statut)->toBe(409);
    expect($corps['error']['code'])->toBe('COMMANDE_EN_ATTENTE');
});

test('répond 401 sans utilisateur authentifié', function () {
    $this->service->expects($this->never())->method('creerCommande');

    [$statut] = appelerCreationCommande($this->action, null);

    expect($statut)->toBe(401);
});
