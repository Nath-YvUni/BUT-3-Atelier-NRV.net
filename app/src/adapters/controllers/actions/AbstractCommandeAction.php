<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\ports\api\ServiceCommandeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

abstract class AbstractCommandeAction extends AbstractAction
{
    public function __construct(
        protected ServiceCommandeInterface $serviceCommande
    ) {
    }

    protected function commandeId(ServerRequestInterface $rq, array $args): int
    {
        $id = $args['id'] ?? '';

        $identifiant = is_string($id) && ctype_digit($id)
            ? filter_var(
                $id,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 2147483647]]
            )
            : false;

        if ($identifiant === false) {
            throw new HttpNotFoundException($rq, 'Cette commande n’existe pas.');
        }

        return $identifiant;
    }

    protected function erreur(
        ResponseInterface $rs,
        int $statut,
        string $code,
        string $message,
        array $details = []
    ): ResponseInterface {
        return $this->json($rs, [
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $statut);
    }
}
