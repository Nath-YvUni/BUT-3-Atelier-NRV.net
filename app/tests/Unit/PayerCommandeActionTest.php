<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\PayerCommandeAction;
use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\PlacesInsuffisantesException;
use nrv\application\exceptions\ValidationException;
use nrv\application\ports\api\ServiceCommandeInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

const CARTE_DE_TEST = '{"numero_carte":"4242424242424242","expiration":"2028-12","cryptogramme":"123"}';

function appelerPaiement(
    PayerCommandeAction $action,
    string $corps = CARTE_DE_TEST,
    string $id = '3',
    string $type = 'application/json',
    ?array $utilisateur = ['id' => 3, 'role' => 'utilisateur']
): array {
    $requete = (new ServerRequestFactory())
        ->createServerRequest('POST', '/api/commandes/' . $id . '/paiement')
        ->withHeader('Content-Type', $type)
        ->withAttribute('utilisateur', $utilisateur);

    $requete->getBody()->write($corps);

    $reponse = $action($requete, (new ResponseFactory())->createResponse(), ['id' => $id]);

    return [
        $reponse->getStatusCode(),
        json_decode((string) $reponse->getBody(), true),
    ];
}

beforeEach(function () {
    $this->service = $this->createMock(ServiceCommandeInterface::class);
    $this->action = new PayerCommandeAction($this->service);
});

test('répond 200 avec le résultat du paiement et les liens vers la commande et les billets', function () {
    $paiement = [
        'id' => 3,
        'reference' => 'CMD-2026-0003',
        'statut' => 'payee',
        'montant_total' => '167.75',
        'devise' => 'EUR',
        'nombre_billets' => 6,
    ];

    $this->service->expects($this->once())
        ->method('payerCommande')
        ->with(3, 3, [
            'numero_carte' => '4242424242424242',
            'expiration' => '2028-12',
            'cryptogramme' => '123',
        ])
        ->willReturn($paiement);

    [$statut, $corps] = appelerPaiement($this->action);

    expect($statut)->toBe(200);
    expect($corps['data'])->toBe($paiement);
    expect($corps['_links'])->toBe([
        'self' => ['href' => '/api/commandes/3', 'method' => 'GET'],
        'billets' => ['href' => '/api/billets', 'method' => 'GET'],
    ]);
});

test('répond 422 avec le détail des données de carte invalides', function () {
    $this->service->method('payerCommande')
        ->willThrowException(new ValidationException(
            ['numero_carte' => 'Le numéro de carte doit contenir 16 chiffres.'],
            'Données invalides.'
        ));

    [$statut, $corps] = appelerPaiement($this->action, '{"numero_carte":"4242"}');

    expect($statut)->toBe(422);
    expect($corps['error']['code'])->toBe('VALIDATION_ERROR');
    expect($corps['error']['details'])->toBe([
        ['champ' => 'numero_carte', 'message' => 'Le numéro de carte doit contenir 16 chiffres.'],
    ]);
});

test('répond 409 avec les soirées qui n\'ont pas assez de places', function () {
    $soirees = [[
        'soiree_id' => 4,
        'nom' => 'Chansons d\'ici',
        'quantite_demandee' => 2,
        'places_disponibles' => 0,
    ]];

    $this->service->method('payerCommande')
        ->willThrowException(new PlacesInsuffisantesException($soirees));

    [$statut, $corps] = appelerPaiement($this->action);

    expect($statut)->toBe(409);
    expect($corps['error']['code'])->toBe('PLACES_INSUFFISANTES');
    expect($corps['error']['details'])->toBe($soirees);
});

test('répond 404 quand la commande n\'existe pas ou appartient à un autre compte', function () {
    $this->service->method('payerCommande')
        ->willThrowException(new CommandeIntrouvableException());

    expect(fn() => appelerPaiement($this->action, id: '99'))
        ->toThrow(HttpNotFoundException::class);
});

test('répond 404 quand l\'identifiant est invalide', function () {
    $this->service->expects($this->never())->method('payerCommande');

    expect(fn() => appelerPaiement($this->action, id: 'abc'))
        ->toThrow(HttpNotFoundException::class);
});

test('répond 415 quand le corps n\'est pas du JSON', function () {
    $this->service->expects($this->never())->method('payerCommande');

    [$statut, $corps] = appelerPaiement($this->action, 'numero_carte=4242', type: 'application/x-www-form-urlencoded');

    expect($statut)->toBe(415);
    expect($corps['error']['code'])->toBe('FORMAT_NON_ACCEPTE');
});

test('répond 400 quand le JSON est mal formé ou n\'est pas un objet', function (string $corps) {
    $this->service->expects($this->never())->method('payerCommande');

    [$statut, $reponse] = appelerPaiement($this->action, $corps);

    expect($statut)->toBe(400);
    expect($reponse['error']['code'])->toBe('JSON_INVALIDE');
})->with(['{"numero_carte":', '[1, 2]', '"texte"']);

test('répond 401 sans utilisateur authentifié', function () {
    $this->service->expects($this->never())->method('payerCommande');

    [$statut] = appelerPaiement($this->action, utilisateur: null);

    expect($statut)->toBe(401);
});
