<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\middlewares;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class GestionnaireMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $utilisateur = $request->getAttribute('utilisateur');

        if (
            !is_array($utilisateur)
            || ($utilisateur['role'] ?? null) !== 'gestionnaire'
        ) {
            return JsonRenderer::render(
                $this->responseFactory->createResponse(),
                403,
                [
                    'error' => [
                        'code' => 'ACCES_INTERDIT',
                        'message' => 'Accès réservé aux gestionnaires.',
                        'details' => [],
                    ],
                ]
            );
        }

        return $handler->handle($request);
    }
}