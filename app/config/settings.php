<?php
declare(strict_types=1);

return [
    'nrv.db.driver' => $_ENV['lib.driver'],
    'nrv.db.host' => $_ENV['lib.host'],
    'nrv.db.database' => $_ENV['lib.database'],
    'nrv.db.username' => $_ENV['lib.username'],
    'nrv.db.password' => $_ENV['lib.password'],
    'nrv.jwt.secret' => $_ENV['jwt.secret'] ?? '',
    'nrv.jwt.ttl' => (int) ($_ENV['jwt.ttl'] ?? 3600),
];
