<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\AjouterPassPanierAction;
use nrv\application\exceptions\PanierVerrouilleException;
use nrv\application\exceptions\ValidationException;
use nrv\application\ports\api\ServicePanierInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

function appelerAjoutPass(
    AjouterPassPanierAction $action,
    string $corps,
    ?array $utilisateur = ['id' => 3, 'role' => 'utilisateur']
): array {
    $requete = (new ServerRequestFactory())
        ->createServerRequest('POST', '/api/panier/pass')
        ->withHeader('Content-Type', 'application/json')
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
    $this->action = new AjouterPassPanierAction($this->service);
    $this->panier = ['id' => 1, 'pass' => [], 'montant_total' => '101.85'];
});

test('répond 201 avec le panier quand le pass est ajouté', function () {
    $this->service->expects($this->once())
        ->method('ajouterPass')
        ->with(3, ['soiree_ids' => [1, 3, 7], 'tarif_id' => 1])
        ->willReturn($this->panier);

    [$statut, $corps] = appelerAjoutPass(
        $this->action,
        '{"soiree_ids":[1,3,7],"tarif_id":1}'
    );

    expect($statut)->toBe(201);
    expect($corps['data'])->toBe($this->panier);
    expect($corps['_links']['commander']['href'])->toBe('/api/commandes');
});

test('répond 422 avec le détail des champs invalides', function () {
    $this->service->method('ajouterPass')
        ->willThrowException(new ValidationException(
            ['soiree_ids' => 'Les trois soirées doivent être différentes.'],
            'Données invalides.'
        ));

    [$statut, $corps] = appelerAjoutPass(
        $this->action,
        '{"soiree_ids":[1,1,7],"tarif_id":1}'
    );

    expect($statut)->toBe(422);
    expect($corps['error']['code'])->toBe('VALIDATION_ERROR');
    expect($corps['error']['details'][0]['champ'])->toBe('soiree_ids');
});

test('répond 409 quand le panier est verrouillé', function () {
    $this->service->method('ajouterPass')
        ->willThrowException(new PanierVerrouilleException());

    [$statut, $corps] = appelerAjoutPass(
        $this->action,
        '{"soiree_ids":[1,3,7],"tarif_id":1}'
    );

    expect($statut)->toBe(409);
    expect($corps['error']['code'])->toBe('PANIER_VERROUILLE');
});

test('répond 401 sans utilisateur authentifié', function () {
    $this->service->expects($this->never())->method('ajouterPass');

    [$statut] = appelerAjoutPass(
        $this->action,
        '{"soiree_ids":[1,3,7],"tarif_id":1}',
        null
    );

    expect($statut)->toBe(401);
});
