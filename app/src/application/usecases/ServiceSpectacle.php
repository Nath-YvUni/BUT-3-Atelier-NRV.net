<?php
declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\application\dto\SpectacleDTO;
use nrv\application\exceptions\ValidationException;

class ServiceSpectacle implements ServiceSpectacleInterface
{
    public function __construct(
        private SpectacleRepositoryInterface $spectacleRepository
    ) {
    }

    public function getSpectacles(?string $date = null): array
    {
        $dateFiltre = null;

        if ($date !== null && $date !== '') {
            if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $date) !== 1) {
                throw new ValidationException(
                    ['date' => 'La date doit respecter le format AAAA-MM-JJ.'],
                    'Date invalide.'
                );
            }

            $dateFiltre = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

            if (
                $dateFiltre === false
                || $dateFiltre->format('Y-m-d') !== $date
            ) {
                throw new ValidationException(
                    ['date' => 'La date indiquée n’existe pas.'],
                    'Date invalide.'
                );
            }
        }

        $spectacles = $this->spectacleRepository->getAll($dateFiltre);

        $dtos = [];

        foreach ($spectacles as $spectacle) {
            $dtos[] = SpectacleDTO::fromEntity($spectacle)->toArray();
        }

        return $dtos;
    }
}