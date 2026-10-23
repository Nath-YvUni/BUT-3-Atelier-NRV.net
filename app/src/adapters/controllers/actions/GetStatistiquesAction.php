<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\ports\api\ServiceStatistiqueInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetStatistiquesAction extends AbstractAction
{
    public function __construct(
        private ServiceStatistiqueInterface $serviceStatistique
    ) {
    }

    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {
        return $this->json($rs, [
            'data' => $this->serviceStatistique->getStatistiques(),
        ]);
    }
}