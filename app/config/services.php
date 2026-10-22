<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;

use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\adapters\spi\repositories\PDOSpectacleRepository;
use nrv\application\usecases\ServiceSpectacle;

use nrv\application\usecases\ServiceSoiree;
use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\adapters\spi\repositories\SoireeRepository;
use nrv\adapters\controllers\actions\GetDetailsSoireeAction;

use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use nrv\application\ports\spi\GestionJetonInterface;
use nrv\application\ports\api\ServiceAuthInterface;
use nrv\application\usecases\ServiceAuth;
use nrv\adapters\spi\repositories\PDOUtilisateurRepository;
use nrv\adapters\security\GestionJetonJWT;

use nrv\application\ports\spi\PanierRepositoryInterface;
use nrv\application\ports\spi\TarifRepositoryInterface;
use nrv\application\ports\api\ServicePanierInterface;
use nrv\application\usecases\ServicePanier;
use nrv\application\ports\api\ServiceTarifInterface;
use nrv\application\usecases\ServiceTarif;
use nrv\adapters\spi\repositories\PDOPanierRepository;
use nrv\adapters\spi\repositories\PDOTarifRepository;

// ── services métier et repositories ─
return [

    'nrv.pdo' => function (ContainerInterface $c) {
        $dsn = "{$c->get('nrv.db.driver')}:host={$c->get('nrv.db.host')};dbname={$c->get('nrv.db.database')}";
        $user = $c->get('nrv.db.username');
        $password = $c->get('nrv.db.password');
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

        // F1
    SpectacleRepositoryInterface::class => function (ContainerInterface $c) {
        return new PDOSpectacleRepository($c->get('nrv.pdo'));
    },
    ServiceSpectacleInterface::class => function (ContainerInterface $c) {
        return new ServiceSpectacle($c->get(SpectacleRepositoryInterface::class));
    },

        // F2
    SoireeRepositoryInterface::class => function (ContainerInterface $c) {
        return new SoireeRepository($c->get('nrv.pdo'));
    },
    ServiceSoireeInterface::class => function (ContainerInterface $c) {
        return new ServiceSoiree($c->get(SoireeRepositoryInterface::class));
    },
        // Authentification
    UtilisateurRepositoryInterface::class => function (ContainerInterface $c) {
        return new PDOUtilisateurRepository(
            $c->get('nrv.pdo')
        );
    },

    GestionJetonInterface::class => function (ContainerInterface $c) {
        return new GestionJetonJWT(
            $c->get('nrv.jwt.secret'),
            $c->get('nrv.jwt.ttl')
        );
    },

    ServiceAuthInterface::class => function (ContainerInterface $c) {
        return new ServiceAuth(
            $c->get(UtilisateurRepositoryInterface::class),
            $c->get(GestionJetonInterface::class)
        );
    },

    TarifRepositoryInterface::class => function (ContainerInterface $c) {
        return new PDOTarifRepository($c->get('nrv.pdo'));
    },
    ServiceTarifInterface::class => function (ContainerInterface $c) {
        return new ServiceTarif($c->get(TarifRepositoryInterface::class));
    },
    PanierRepositoryInterface::class => function (ContainerInterface $c) {
        return new PDOPanierRepository($c->get('nrv.pdo'));
    },
    ServicePanierInterface::class => function (ContainerInterface $c) {
        return new ServicePanier(
            $c->get(PanierRepositoryInterface::class),
            $c->get(SoireeRepositoryInterface::class),
            $c->get(TarifRepositoryInterface::class)
        );
    },
];
