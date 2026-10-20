<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;

// ── services métier et repositories ─
return [

    'nrv.pdo' => function (ContainerInterface $c) {
        $dsn = "{$c->get('nrv.db.driver')}:host={$c->get('nrv.db.host')};dbname={$c->get('nrv.db.database')}";
        $user = $c->get('nrv.db.username');
        $password = $c->get('nrv.db.password');
        return new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    },

];
