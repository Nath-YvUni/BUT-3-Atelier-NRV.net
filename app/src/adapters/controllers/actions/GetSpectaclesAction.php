<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\ports\api\ServiceSpectacleInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetSpectaclesAction extends AbstractAction {

    private ServiceSpectacleInterface $serviceSpectacle;

    function __construct(ServiceSpectacleInterface $serviceSpectacle) {
        $this->serviceSpectacle = $serviceSpectacle;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
        $spectacles = $this->serviceSpectacle->getSpectacles();
        return $this->json($rs, $spectacles);
    }
}
