<?php
declare(strict_types=1);

namespace nrv\adapters\config;

use DI\ContainerBuilder;

final class ContainerConfig {

    public static function build(): \DI\Container {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->addDefinitions(__DIR__ . '/../../../config/settings.php');
        $builder->addDefinitions(__DIR__ . '/../../../config/services.php');
        $builder->addDefinitions(__DIR__ . '/../../../config/api.php');
        return $builder->build();
    }
}
