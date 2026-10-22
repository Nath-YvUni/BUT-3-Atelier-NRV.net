<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\ports\api\ServicePanierInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ModifierQuantitePanierAction extends AbstractAction
{
    public function __construct(
        private ServicePanierInterface $servicePanier
    ) {
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $utilisateur = $rq->getAttribute('utilisateur');

        if (!is_array($utilisateur) || !isset($utilisateur['id'])) {
            return $this->json($rs, [
                'error' => [
                    'code' => 'AUTHENTIFICATION_REQUISE',
                    'message' => 'Authentification requise.',
                    'details' => [],
                ],
            ], 401);
        }

        $donnees = $rq->getParsedBody();

        if (!is_array($donnees)) {
            $donnees = [];
        }

        $panier = $this->servicePanier->modifierQuantite(
            (int) $utilisateur['id'],
            (int) $args['id'],
            $donnees
        );

        return $this->json($rs, [
            'data' => $panier,
        ]);
    }
}