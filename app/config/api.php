<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;

use nrv\adapters\controllers\actions\GetDetailsSoireeAction;
use nrv\application\ports\api\ServiceSoireeInterface;

return [


    GetDetailsSoireeAction::class => function (ContainerInterface $c) {
        return new GetDetailsSoireeAction($c->get(ServiceSoireeInterface::class));
    },

];