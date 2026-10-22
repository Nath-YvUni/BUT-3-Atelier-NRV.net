<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;

use nrv\adapters\controllers\actions\GetSpectaclesAction;
use nrv\application\ports\api\ServiceSpectacleInterface;

use nrv\adapters\controllers\actions\GetDetailsSoireeAction;
use nrv\application\ports\api\ServiceSoireeInterface;

use nrv\adapters\controllers\actions\InscriptionAction;
use nrv\adapters\controllers\actions\ConnexionAction;
use nrv\adapters\controllers\actions\ProfilAction;
use nrv\adapters\controllers\middlewares\AuthentificationMiddleware;
use nrv\application\ports\api\ServiceAuthInterface;
use nrv\application\ports\spi\GestionJetonInterface;
use Slim\Psr7\Factory\ResponseFactory;

use nrv\adapters\controllers\actions\AjouterLignePanierAction;
use nrv\adapters\controllers\actions\AjouterPassPanierAction;
use nrv\application\ports\api\ServicePanierInterface;

use nrv\adapters\controllers\actions\GetTarifsAction;
use nrv\application\ports\api\ServiceTarifInterface;

return [

    GetSpectaclesAction::class => function (ContainerInterface $c) {
        return new GetSpectaclesAction($c->get(ServiceSpectacleInterface::class));
    },


    GetDetailsSoireeAction::class => function (ContainerInterface $c) {
        return new GetDetailsSoireeAction($c->get(ServiceSoireeInterface::class));
    },

    InscriptionAction::class => function (ContainerInterface $c) {
        return new InscriptionAction(
            $c->get(ServiceAuthInterface::class)
        );
    },

    ConnexionAction::class => function (ContainerInterface $c) {
        return new ConnexionAction(
            $c->get(ServiceAuthInterface::class)
        );
    },

    ProfilAction::class => function (ContainerInterface $c) {
        return new ProfilAction();
    },

    GetTarifsAction::class => function (ContainerInterface $c) {
        return new GetTarifsAction($c->get(ServiceTarifInterface::class));
    },

    AjouterLignePanierAction::class => function (ContainerInterface $c) {
        return new AjouterLignePanierAction(
            $c->get(ServicePanierInterface::class)
        );
    },

    AjouterPassPanierAction::class => function (ContainerInterface $c) {
        return new AjouterPassPanierAction(
            $c->get(ServicePanierInterface::class)
        );
    },

    AuthentificationMiddleware::class => function (ContainerInterface $c) {
        return new AuthentificationMiddleware(
            $c->get(GestionJetonInterface::class),
            $c->get(ServiceAuthInterface::class),
            new ResponseFactory()
        );
    },
];