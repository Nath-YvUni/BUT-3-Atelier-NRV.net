<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;

use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\adapters\spi\repositories\PDOSpectacleRepository;
use nrv\application\usecases\ServiceSpectacle;

// ── services métier et repositories ─
return [

    'nrv.pdo' => function (ContainerInterface $c) {
        $dsn = "{$c->get('nrv.db.driver')}:host={$c->get('nrv.db.host')};dbname={$c->get('nrv.db.database')}";
        $user = $c->get('nrv.db.username');
        $password = $c->get('nrv.db.password');
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

    SpectacleRepositoryInterface::class => function (ContainerInterface $c) {
        return new PDOSpectacleRepository($c->get('nrv.pdo'));
    },

    ServiceSpectacleInterface::class => function (ContainerInterface $c) {
        return new ServiceSpectacle($c->get(SpectacleRepositoryInterface::class));
    },

];
