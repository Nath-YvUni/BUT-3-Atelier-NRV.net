<?php
declare(strict_types=1);

use nrv\adapters\controllers\actions\GetBilletByTokenAction;
use nrv\application\exceptions\BilletIntrouvableException;
use nrv\application\ports\api\ServiceBilletInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

function appelerBillet(GetBilletByTokenAction $action, array $args)
{
    $requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/billet/x');

    return $action(
        $requete,
        (new ResponseFactory())->createResponse(),
        $args
    );
}

beforeEach(function () {
    $this->service = $this->createMock(ServiceBilletInterface::class);
    $this->action = new GetBilletByTokenAction($this->service);
    $this->token = str_repeat('ab', 32);
});

test('répond 200 avec le billet et interdit le cache partagé', function () {
    $billet = [
        'reference' => 'BIL-2026-0001',
        'token' => $this->token,
        'acheteur' => 'Alice Martin'
    ];

    $this->service->expects($this->once())
        ->method('consulterBillet')
        ->with($this->token)
        ->willReturn($billet);

    $reponse = appelerBillet($this->action, [
        'token' => $this->token
    ]);

    $corps = json_decode((string) $reponse->getBody(), true);

    expect($reponse->getStatusCode())->toBe(200);
    expect($corps['data'])->toBe($billet);
    expect($corps['_links']['self']['href'])
        ->toBe('/api/billets/' . $this->token);
    expect($reponse->getHeaderLine('Cache-Control'))
        ->toContain('no-store');
});

test('répond 404 quand le billet n\'existe pas', function () {
    $this->service->method('consulterBillet')
        ->willThrowException(new BilletIntrouvableException());

    expect(fn() => appelerBillet($this->action, [
        'token' => $this->token
    ]))->toThrow(HttpNotFoundException::class);
});
