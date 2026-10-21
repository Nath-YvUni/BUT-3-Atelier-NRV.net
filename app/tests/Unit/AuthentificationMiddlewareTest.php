<?php
declare(strict_types=1);

use nrv\adapters\controllers\middlewares\AuthentificationMiddleware;
use nrv\application\exceptions\AuthentificationException;
use nrv\application\exceptions\RepositoryInternalException;
use nrv\application\ports\api\ServiceAuthInterface;
use nrv\application\ports\spi\GestionJetonInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

beforeEach(function () {
    $this->jetons = $this->createMock(
        GestionJetonInterface::class
    );

    $this->serviceAuth = $this->createMock(
        ServiceAuthInterface::class
    );

    $this->handler = $this->createMock(
        RequestHandlerInterface::class
    );

    $this->middleware = new AuthentificationMiddleware(
        $this->jetons,
        $this->serviceAuth,
        new ResponseFactory()
    );

    $this->requete = (new ServerRequestFactory())
        ->createServerRequest('GET', '/api/me');
});

test('autorisation absente ou mal formée : refuse la requête', function (string $autorisation) {
    $this->jetons->expects($this->never())
        ->method('lire');

    $this->serviceAuth->expects($this->never())
        ->method('profil');

    $this->handler->expects($this->never())
        ->method('handle');

    $requete = $this->requete;

    if ($autorisation !== '') {
        $requete = $requete->withHeader(
            'Authorization',
            $autorisation
        );
    }

    $reponse = $this->middleware->process(
        $requete,
        $this->handler
    );

    expect($reponse->getStatusCode())->toBe(401);

    expect($reponse->getHeaderLine('WWW-Authenticate'))
        ->toContain('Bearer');
})->with([
            'sans en-tête' => [''],
            'mauvais mécanisme' => ['Basic identifiants'],
            'Bearer sans jeton' => ['Bearer'],
        ]);

test('jeton invalide : refuse la requête', function () {
    $this->jetons->method('lire')
        ->willThrowException(new AuthentificationException(
            'Jeton invalide.'
        ));

    $this->serviceAuth->expects($this->never())
        ->method('profil');

    $this->handler->expects($this->never())
        ->method('handle');

    $requete = $this->requete->withHeader(
        'Authorization',
        'Bearer jeton-invalide'
    );

    $reponse = $this->middleware->process(
        $requete,
        $this->handler
    );

    expect($reponse->getStatusCode())->toBe(401);
});

test('jeton valide : transmet le profil à la suite du traitement', function () {
    $profil = [
        'id' => 3,
        'nom' => 'Bob Durand',
        'email' => 'bob@example.com',
        'role' => 'utilisateur',
    ];

    $this->jetons->expects($this->once())
        ->method('lire')
        ->with('jeton-valide')
        ->willReturn(3);

    $this->serviceAuth->expects($this->once())
        ->method('profil')
        ->with(3)
        ->willReturn($profil);

    $reponseAttendue = (new ResponseFactory())
        ->createResponse(200);

    $this->handler->expects($this->once())
        ->method('handle')
        ->with($this->callback(
            static fn(ServerRequestInterface $requete): bool =>
                $requete->getAttribute('utilisateur') === $profil
        ))
        ->willReturn($reponseAttendue);

    $requete = $this->requete->withHeader(
        'Authorization',
        'Bearer jeton-valide'
    );

    $reponse = $this->middleware->process(
        $requete,
        $this->handler
    );

    expect($reponse)->toBe($reponseAttendue);
});

test('utilisateur supprimé : refuse un jeton pourtant valide', function () {
    $this->jetons->method('lire')->willReturn(3);

    $this->serviceAuth->method('profil')
        ->willThrowException(new AuthentificationException(
            'Utilisateur introuvable.'
        ));

    $this->handler->expects($this->never())
        ->method('handle');

    $requete = $this->requete->withHeader(
        'Authorization',
        'Bearer jeton-valide'
    );

    $reponse = $this->middleware->process(
        $requete,
        $this->handler
    );

    expect($reponse->getStatusCode())->toBe(401);
});

test('une erreur de BDD reste une erreur technique', function () {
    $this->jetons->method('lire')->willReturn(3);

    $this->serviceAuth->method('profil')
        ->willThrowException(new RepositoryInternalException(
            'Base indisponible.'
        ));

    $this->handler->expects($this->never())
        ->method('handle');

    $requete = $this->requete->withHeader(
        'Authorization',
        'Bearer jeton-valide'
    );

    expect(fn() => $this->middleware->process(
        $requete,
        $this->handler
    ))->toThrow(RepositoryInternalException::class);
});