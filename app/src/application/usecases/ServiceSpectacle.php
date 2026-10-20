<?php
declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\application\dto\SpectacleDTO;

class ServiceSpectacle implements ServiceSpectacleInterface
{
    public function __construct(private SpectacleRepositoryInterface $spectacleRepository)
    {
    }

    public function getSpectacles(): array
    {
        $spectacles = $this->spectacleRepository->getAll();

        $dtos = [];
        foreach ($spectacles as $spectacle) {
            $dtos[] = SpectacleDTO::fromEntity($spectacle)->toArray();
        }

        return $dtos;
    }
}
