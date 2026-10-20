<?php
namespace nrv\adapters\controllers\actions;

use Throwable;
use nrv\adapters\controllers\actions\AbstractAction;
use nrv\application\ports\api\ServiceSpectacleInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpInternalServerErrorException;

class GetSpectaclesAction extends AbstractAction{

    private ServiceSpectacleInterface $serviceSpectacleInterface;

    public function __construct(ServiceSpectacleInterface $serviceSpectacleInterface) {
        $this->serviceSpectacleInterface = $serviceSpectacleInterface;
    }

         public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface {
            try {
                $spectacleDTO = $this->serviceSpectacleInterface->get();
            } catch (Throwable $e){
                throw new HttpInternalServerErrorException($rq,'Erreur lors de la récupération des spectacles', $e
                );
            }
            return $this->json($rs, $spectacleDTO->toArray());
         }


}


?>