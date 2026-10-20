<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;

use nrv\application\usecases\ServiceSoiree;
use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\adapters\persistance\SoireeRepository;
use nrv\adapters\controllers\actions\GetDetailsSoireeAction;

// ── services métier et repositories ─
return [

    'nrv.pdo' => function (ContainerInterface $c) {
        $dsn = "{$c->get('nrv.db.driver')}:host={$c->get('nrv.db.host')};dbname={$c->get('nrv.db.database')}";
        $user = $c->get('nrv.db.username');
        $password = $c->get('nrv.db.password');
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

        SoireeRepositoryInterface::class => \DI\create(SoireeRepository::class)
        ->constructor(\DI\get('nrv.pdo')),

    ServiceSoireeInterface::class => \DI\create(ServiceSoiree::class)
        ->constructor(\DI\get(SoireeRepositoryInterface::class)),

    GetDetailsSoireeAction::class => \DI\create(GetDetailsSoireeAction::class)
        ->constructor(\DI\get(ServiceSoireeInterface::class)),

];
