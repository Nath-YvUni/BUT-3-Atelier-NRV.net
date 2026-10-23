<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\exceptions\SoireeIntrouvableException;


class AjouterCommandeAction extends AbstractAction
{

    private ServiceCommandeInterface $serviceCommande;
    private int $utilisateurId;
    private array $donnees;

    public function __construct(ServiceCommandeInterface $serviceCommande, int $utilisateurId, array $donnees)
    {
        $this->serviceCommande = $serviceCommande;
        $this->utilisateurId = $utilisateurId;
        $this->donnees = $donnees;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $this->serviceCommande->commander();
    }
}