<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;

use nrv\adapters\controllers\actions\GetSpectaclesAction;
use nrv\application\ports\api\ServiceSpectacleInterface;

use nrv\adapters\controllers\actions\GetDetailsSoireeAction;
use nrv\application\ports\api\ServiceSoireeInterface;

return [

    GetSpectaclesAction::class => function (ContainerInterface $c) {
        return new GetSpectaclesAction($c->get(ServiceSpectacleInterface::class));
    },


    GetDetailsSoireeAction::class => function (ContainerInterface $c) {
        return new GetDetailsSoireeAction($c->get(ServiceSoireeInterface::class));
    },

];