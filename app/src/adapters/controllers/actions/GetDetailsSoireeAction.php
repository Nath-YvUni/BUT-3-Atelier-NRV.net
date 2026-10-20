<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\exceptions\SoireeIntrouvableException;


class GetDetailsSoireeAction extends AbstractAction {

    private ServiceSoireeInterface $serviceSoiree;

    public function __construct(ServiceSoireeInterface $serviceSoiree) {
        $this->serviceSoiree = $serviceSoiree;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $id = $args['id'];

        try {
            $soireeDTO = $this->serviceSoiree->getDetails((int) $id);
        } catch (SoireeIntrouvableException $e) {
            throw new HttpNotFoundException($rq, "Aucune soiree avec l'id $id");
        }
        return $this->json($rs, $soireeDTO->toArray());
    }

}