<?php
declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\dto\TarifDTO;
use nrv\application\ports\api\ServiceTarifInterface;
use nrv\application\ports\spi\TarifRepositoryInterface;

final class ServiceTarif implements ServiceTarifInterface
{
    public function __construct(
        private TarifRepositoryInterface $tarifs
    ) {
    }

    public function getTarifs(): array
    {
        $dtos = [];

        foreach ($this->tarifs->trouverTous() as $tarif) {
            $dtos[] = TarifDTO::fromEntity($tarif)->toArray();
        }

        return $dtos;
    }
}
