<?php
declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Tarif;

interface TarifRepositoryInterface
{
    public function trouverParId(int $id): ?Tarif;
}
