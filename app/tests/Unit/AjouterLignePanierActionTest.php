<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\AjouterLignePanierAction;
use nrv\application\exceptions\PanierVerrouilleException;
use nrv\application\exceptions\ValidationException;
use nrv\application\ports\api\ServicePanierInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

function appelerAjoutPanier(
    AjouterLignePanierAction $action,
    string $corps,
    string $type = 'application/json',
    ?array $utilisateur = ['id' => 3, 'role' => 'utilisateur']
): array {
    $requete = (new ServerRequestFactory())
        ->createServerRequest('POST', '/api/panier/lignes')
        ->withHeader('Content-Type', $type)
        ->withAttribute('utilisateur', $utilisateur);

    $requete->getBody()->write($corps);

    $reponse = $action($requete, (new ResponseFactory())->createResponse(), []);

    return [
        $reponse->getStatusCode(),
        json_decode((string) $reponse->getBody(), true),
    ];
}

beforeEach(function () {
    $this->service = $this->createMock(ServicePanierInterface::class);
    $this->action = new AjouterLignePanierAction($this->service);
    $this->panier = ['id' => 1, 'lignes' => [], 'montant_total' => '70.00'];
});

test('répond 201 avec le panier quand une ligne est créée', function () {
    $this->service->expects($this->once())
        ->method('ajouterBillets')
        ->with(3, ['soiree_id' => 2, 'tarif_id' => 1, 'quantite' => 2])
        ->willReturn(['cree' => true, 'panier' => $this->panier]);

    [$statut, $corps] = appelerAjoutPanier(
        $this->action,
        '{"soiree_id":2,"tarif_id":1,"quantite":2}'
    );

    expect($statut)->toBe(201);
    expect($corps['data'])->toBe($this->panier);
    expect($corps['_links']['self']['href'])->toBe('/api/panier');
});

test('répond 200 quand une ligne existante est augmentée', function () {
    $this->service->method('ajouterBillets')
        ->willReturn(['cree' => false, 'panier' => $this->panier]);

    [$statut] = appelerAjoutPanier(
        $this->action,
        '{"soiree_id":2,"tarif_id":1,"quantite":2}'
    );

    expect($statut)->toBe(200);
});

test('répond 422 avec le détail des champs invalides', function () {
    $this->service->method('ajouterBillets')
        ->willThrowException(new ValidationException(
            ['quantite' => 'Un entier strictement positif est obligatoire.'],
            'Données invalides.'
        ));

    [$statut, $corps] = appelerAjoutPanier(
        $this->action,
        '{"soiree_id":2,"tarif_id":1,"quantite":0}'
    );

    expect($statut)->toBe(422);
    expect($corps['error']['code'])->toBe('VALIDATION_ERROR');
    expect($corps['error']['details'][0]['champ'])->toBe('quantite');
});

test('répond 409 quand le panier est verrouillé', function () {
    $this->service->method('ajouterBillets')
        ->willThrowException(new PanierVerrouilleException());

    [$statut, $corps] = appelerAjoutPanier(
        $this->action,
        '{"soiree_id":2,"tarif_id":1,"quantite":1}'
    );

    expect($statut)->toBe(409);
    expect($corps['error']['code'])->toBe('PANIER_VERROUILLE');
});

test('répond 415 quand le corps n\'est pas du JSON', function () {
    $this->service->expects($this->never())->method('ajouterBillets');

    [$statut] = appelerAjoutPanier($this->action, 'soiree_id=2', 'application/x-www-form-urlencoded');

    expect($statut)->toBe(415);
});

test('répond 400 quand le JSON est mal formé ou n\'est pas un objet', function (string $corps) {
    $this->service->expects($this->never())->method('ajouterBillets');

    [$statut, $reponse] = appelerAjoutPanier($this->action, $corps);

    expect($statut)->toBe(400);
    expect($reponse['error']['code'])->toBe('JSON_INVALIDE');
})->with(['{"soiree_id":2', '[1, 2]', '"texte"']);

test('répond 401 sans utilisateur authentifié', function () {
    $this->service->expects($this->never())->method('ajouterBillets');

    [$statut] = appelerAjoutPanier(
        $this->action,
        '{"soiree_id":2,"tarif_id":1,"quantite":1}',
        utilisateur: null
    );

    expect($statut)->toBe(401);
});
