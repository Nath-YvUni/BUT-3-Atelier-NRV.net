<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\ports\api\ServiceTarifInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetTarifsAction extends AbstractAction
{
    public function __construct(
        private ServiceTarifInterface $serviceTarif
    ) {
    }

    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {
        return $this->json($rs, [
            'data' => $this->serviceTarif->getTarifs(),
        ]);
    }
}
