<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\ports\api\ServicePanierInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetPanierAction extends AbstractAction
{
    public function __construct(
        private ServicePanierInterface $servicePanier
    ) {
    }

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
            'data' => $this->servicePanier->consulterPanier(
                (int) $utilisateur['id']
            ),
            '_links' => [
                'self' => [
                    'href' => '/api/panier',
                    'method' => 'GET',
                ],
                'commander' => [
                    'href' => '/api/commandes',
                    'method' => 'POST',
                ],
            ],
        ]);
    }
}
