<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\GetCommandeAction;
use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\ports\api\ServiceCommandeInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

function appelerDetailCommande(
    GetCommandeAction $action,
    string $id,
    ?array $utilisateur = ['id' => 3, 'role' => 'utilisateur']
): array {
    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/commandes/' . $id)
        ->withAttribute('utilisateur', $utilisateur);

    $reponse = $action($requete, (new ResponseFactory())->createResponse(), ['id' => $id]);

    return [
        $reponse->getStatusCode(),
        json_decode((string) $reponse->getBody(), true),
    ];
}

beforeEach(function () {
    $this->service = $this->createMock(ServiceCommandeInterface::class);
    $this->action = new GetCommandeAction($this->service);
});

test('répond 200 avec la commande et le lien de paiement quand elle est en attente', function () {
    $commande = ['id' => 3, 'reference' => 'CMD-2026-0003', 'statut' => 'en_attente'];

    $this->service->expects($this->once())
        ->method('getCommandeById')
        ->with(3, 3)
        ->willReturn($commande);

    [$statut, $corps] = appelerDetailCommande($this->action, '3');

    expect($statut)->toBe(200);
    expect($corps['data'])->toBe($commande);
    expect($corps['_links'])->toBe([
        'self' => ['href' => '/api/commandes/3', 'method' => 'GET'],
        'paiement' => ['href' => '/api/commandes/3/paiement', 'method' => 'POST'],
    ]);
});

test('donne le lien suivant selon le statut de la commande', function (string $statutCommande, string $lien, string $href) {
    $this->service->method('getCommandeById')
        ->willReturn(['id' => 3, 'statut' => $statutCommande]);

    [$statut, $corps] = appelerDetailCommande($this->action, '3');

    expect($statut)->toBe(200);
    expect($corps['_links'])->toHaveKeys(['self', $lien]);
    expect($corps['_links'][$lien]['href'])->toBe($href);
})->with([
    'payée' => ['payee', 'billets', '/api/billets'],
    'refusée' => ['refusee', 'panier', '/api/panier'],
]);

test('répond 404 quand la commande n\'existe pas ou appartient à un autre compte', function () {
    $this->service->method('getCommandeById')
        ->willThrowException(new CommandeIntrouvableException());

    expect(fn() => appelerDetailCommande($this->action, '99'))
        ->toThrow(HttpNotFoundException::class);
});

test('répond 404 quand l\'identifiant est invalide', function (string $id) {
    $this->service->expects($this->never())->method('getCommandeById');

    expect(fn() => appelerDetailCommande($this->action, $id))
        ->toThrow(HttpNotFoundException::class);
})->with(['abc', '0', '-1', '1.5']);

test('répond 401 sans utilisateur authentifié', function () {
    $this->service->expects($this->never())->method('getCommandeById');

    [$statut, $corps] = appelerDetailCommande($this->action, '3', null);

    expect($statut)->toBe(401);
    expect($corps['error']['code'])->toBe('AUTHENTIFICATION_REQUISE');
});
