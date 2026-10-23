<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\PlacesInsuffisantesException;
use nrv\application\exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

final class PayerCommandeAction extends AbstractCommandeAction
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

        $type = strtolower(trim(explode(';', $rq->getHeaderLine('Content-Type'), 2)[0]));

        if ($type !== 'application/json') {
            return $this->erreur($rs, 415, 'FORMAT_NON_ACCEPTE', 'Le corps doit être envoyé en application/json.');
        }

        try {
            $donnees = json_decode((string) $rq->getBody(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $this->erreur($rs, 400, 'JSON_INVALIDE', 'Le corps JSON est mal formé.');
        }

        if (!$donnees instanceof \stdClass) {
            return $this->erreur($rs, 400, 'JSON_INVALIDE', 'Le corps JSON doit être un objet.');
        }

        try {
            $paiement = $this->serviceCommande->payerCommande((int) $utilisateur['id'], $id, (array) $donnees);
        } catch (CommandeIntrouvableException $e) {
            throw new HttpNotFoundException($rq, $e->getMessage());
        } catch (ValidationException $e) {
            $details = [];

            foreach ($e->getErrors() as $champ => $message) {
                $details[] = ['champ' => $champ, 'message' => $message];
            }

            return $this->erreur($rs, 422, 'VALIDATION_ERROR', $e->getMessage(), $details);
        } catch (PlacesInsuffisantesException $e) {
            return $this->erreur($rs, 409, 'PLACES_INSUFFISANTES', $e->getMessage(), $e->getSoirees());
        }

        return $this->json($rs, [
            'data' => $paiement,
            '_links' => [
                'self' => ['href' => '/api/commandes/' . $id, 'method' => 'GET'],
                'billets' => ['href' => '/api/commandes/' . $id . '/billets', 'method' => 'GET'],
            ],
        ]);
    }
}
