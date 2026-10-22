<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\GetSoireesAction;
use nrv\application\exceptions\ValidationException;
use nrv\application\ports\api\ServiceSoireeInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

function appelerListeSoirees(GetSoireesAction $action, array $parametres = []): array
{
    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/soirees')
        ->withQueryParams($parametres);

    $reponse = $action($requete, (new ResponseFactory())->createResponse(), []);

    return [
        $reponse->getStatusCode(),
        json_decode((string) $reponse->getBody(), true),
    ];
}

beforeEach(function () {
    $this->service = $this->createMock(ServiceSoireeInterface::class);
    $this->action = new GetSoireesAction($this->service);
});

test('répond 200 avec la liste des soirées', function () {
    $this->service->expects($this->once())
        ->method('getSoirees')
        ->with(null)
        ->willReturn([['id' => 2, 'nom' => 'Blues Night']]);

    [$statut, $corps] = appelerListeSoirees($this->action);

    expect($statut)->toBe(200);
    expect($corps['data'])->toBe([['id' => 2, 'nom' => 'Blues Night']]);
    expect($corps['_links']['self']['href'])->toBe('/api/soirees');
});

test('transmet le filtre de date et le reprend dans le lien self', function () {
    $this->service->expects($this->once())
        ->method('getSoirees')
        ->with('2027-06-05')
        ->willReturn([]);

    [$statut, $corps] = appelerListeSoirees($this->action, ['date' => '2027-06-05']);

    expect($statut)->toBe(200);
    expect($corps['data'])->toBe([]);
    expect($corps['_links']['self']['href'])->toBe('/api/soirees?date=2027-06-05');
});

test('répond 422 quand la date est invalide', function () {
    $this->service->method('getSoirees')
        ->willThrowException(new ValidationException(
            ['date' => 'La date doit être une date valide au format AAAA-MM-JJ.'],
            'Date invalide.'
        ));

    [$statut, $corps] = appelerListeSoirees($this->action, ['date' => '2027-02-30']);

    expect($statut)->toBe(422);
    expect($corps['error']['code'])->toBe('VALIDATION_ERROR');
    expect($corps['error']['details'][0]['champ'])->toBe('date');
});

test('répond 422 quand plusieurs dates sont transmises', function () {
    $this->service->expects($this->never())->method('getSoirees');

    [$statut] = appelerListeSoirees($this->action, ['date' => ['2027-06-05', '2027-06-06']]);

    expect($statut)->toBe(422);
});
