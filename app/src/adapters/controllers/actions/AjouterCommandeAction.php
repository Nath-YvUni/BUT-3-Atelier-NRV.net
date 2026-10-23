<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use nrv\application\ports\api\ServiceCommandeInterface;
use nrv\application\exceptions\CommandeEnAttenteException;
use nrv\application\exceptions\PanierVideException;


class AjouterCommandeAction extends AbstractAction
{

    private ServiceCommandeInterface $serviceCommande;

    public function __construct(ServiceCommandeInterface $serviceCommande)
    {
        $this->serviceCommande = $serviceCommande;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $utilisateur = $rq->getAttribute('utilisateur');

        if (!is_array($utilisateur)) {
            return $this->erreur($rs, 401, 'AUTHENTIFICATION_REQUISE', 'Authentification requise.');
        }

        try {
            $commande = $this->serviceCommande->creerCommande((int) $utilisateur['id'], []);
        } catch (PanierVideException $e) {
            return $this->erreur($rs, 422, 'PANIER_VIDE', $e->getMessage());
        } catch (CommandeEnAttenteException $e) {
            return $this->erreur($rs, 409, 'COMMANDE_EN_ATTENTE', $e->getMessage());
        }

        return $this->json($rs, [
            'data' => $commande,
            '_links' => [
                'self' => [
                    'href' => '/api/commandes/' . $commande['id'],
                    'method' => 'GET',
                ],
                'paiement' => [
                    'href' => '/api/commandes/' . $commande['id'] . '/paiement',
                    'method' => 'POST',
                ],
            ],
        ], 201);
    }

    private function erreur(ResponseInterface $rs, int $statut, string $code, string $message): ResponseInterface
    {
        return $this->json($rs, [
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => [],
            ],
        ], $statut);
    }
}
