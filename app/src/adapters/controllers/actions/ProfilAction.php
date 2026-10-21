<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProfilAction extends AbstractAction
{
    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {
        $utilisateur = $rq->getAttribute('utilisateur');

        if (!is_array($utilisateur)) {
            return $this->json($rs, [
                'error' => [
                    'code' => 'AUTHENTIFICATION_REQUISE',
                    'message' => 'Authentification requise.',
                    'details' => [],
                ],
            ], 401);
        }

        return $this->json($rs, [
            'data' => $utilisateur,
            '_links' => [
                'self' => [
                    'href' => '/api/me',
                    'method' => 'GET',
                ],
            ],
        ]);
    }
}