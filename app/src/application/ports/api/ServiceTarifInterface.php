<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

interface ServiceTarifInterface
{
    public function getTarifs(): array;
}
