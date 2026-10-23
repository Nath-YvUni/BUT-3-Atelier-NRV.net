<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\exceptions\CommandeIntrouvableException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

final class GetCommandeAction extends AbstractCommandeAction
{
    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {
        $utilisateur = $rq->getAttribute('utilisateur');

        if (!is_array($utilisateur)) {
            return $this->erreur($rs, 401, 'AUTHENTIFICATION_REQUISE', 'Authentification requise.');
        }

        $id = $this->commandeId($rq, $args);

        try {
            $commande = $this->serviceCommande->getCommandeById((int) $utilisateur['id'], $id);
        } catch (CommandeIntrouvableException $e) {
            throw new HttpNotFoundException($rq, $e->getMessage());
        }

        $liens = [
            'en_attente' => ['paiement' => ['href' => '/api/commandes/' . $id . '/paiement', 'method' => 'POST']],
            'payee' => ['billets' => ['href' => '/api/billets', 'method' => 'GET']],
            'refusee' => ['panier' => ['href' => '/api/panier', 'method' => 'GET']],
        ];

        return $this->json($rs, [
            'data' => $commande,
            '_links' => [
                'self' => ['href' => '/api/commandes/' . $id, 'method' => 'GET'],
                ...$liens[$commande['statut']],
            ],
        ]);
    }
}
