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

    public function getSpectacles(?string $date = null, ?string $styleId = null): array
    {
        $dateFiltre = null;
        $styleFiltre = null;

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

        if ($styleId !== null && $styleId !== '') {
            $identifiant = filter_var(
                $styleId,
                FILTER_VALIDATE_INT,
                [
                    'options' => [
                        'min_range' => 1,
                        'max_range' => 2147483647,
                    ],
                ]
            );

            if (!ctype_digit($styleId) || $identifiant === false) {
                throw new ValidationException(
                    [
                        'style_id' => 'L’identifiant du style doit être un entier strictement positif valide.',
                    ],
                    'Style invalide.'
                );
            }

            $styleFiltre = $identifiant;
        }

        $spectacles = $this->spectacleRepository->getAll($dateFiltre, $styleFiltre);

        $dtos = [];

        foreach ($spectacles as $spectacle) {
            $dtos[] = SpectacleDTO::fromEntity($spectacle)->toArray();
        }

        return $dtos;
    }
}