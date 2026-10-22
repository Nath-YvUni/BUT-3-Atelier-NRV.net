<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\middlewares;

use nrv\application\exceptions\AuthentificationException;
use nrv\application\ports\api\ServiceAuthInterface;
use nrv\application\ports\spi\GestionJetonInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthentificationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private GestionJetonInterface $jetons,
        private ServiceAuthInterface $serviceAuth,
        private ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $autorisation = $request->getHeaderLine('Authorization');

        try {
            if (
                preg_match(
                    '/\ABearer\s+(\S+)\z/i',
                    $autorisation,
                    $correspondances
                ) !== 1
            ) {
                throw new AuthentificationException(
                    'Jeton Bearer obligatoire.'
                );
            }

            $utilisateurId = $this->jetons->lire(
                $correspondances[1]
            );

            $utilisateur = $this->serviceAuth->profil(
                $utilisateurId
            );
        } catch (AuthentificationException $e) {
            $response = $this->responseFactory
                ->createResponse()
                ->withHeader(
                    'WWW-Authenticate',
                    'Bearer realm="nrv.net"'
                );

            return JsonRenderer::render($response, 401, [
                'error' => [
                    'code' => 'AUTHENTIFICATION_REQUISE',
                    'message' => 'Authentification requise ou jeton invalide.',
                    'details' => [],
                ],
            ]);
        }

        return $handler->handle(
            $request->withAttribute('utilisateur', $utilisateur)
        );
    }
}