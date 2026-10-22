<?php
declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\dto\SoireeDTO;
use nrv\application\dto\SoireeProgrammeDTO;
use nrv\application\dto\TarifDTO;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\exceptions\ValidationException;
use nrv\domain\entities\SoireeProgramme;
use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\ports\spi\TarifRepositoryInterface;

class ServiceSoiree implements ServiceSoireeInterface
{
    public function __construct(
        private SoireeRepositoryInterface $soireeRepository,
        private TarifRepositoryInterface $tarifRepository
    ) {
    }

    public function getSoirees(?string $date = null): array
    {
        $dateFiltre = null;

        if ($date !== null && $date !== '') {
            $dateFiltre = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

            if (
                preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $date) !== 1
                || $dateFiltre === false
                || $dateFiltre->format('Y-m-d') !== $date
            ) {
                throw new ValidationException(
                    ['date' => 'La date doit être une date valide au format AAAA-MM-JJ.'],
                    'Date invalide.'
                );
            }
        }

        return array_map(
            fn(SoireeProgramme $soiree): array => SoireeProgrammeDTO::fromEntity($soiree)->toArray(),
            $this->soireeRepository->findByAll($dateFiltre)
        );
    }

    public function getDetails(int $id): SoireeDTO
    {
        $soiree = $this->soireeRepository->findById($id);

        if ($soiree === null) {
            throw new SoireeIntrouvableException();
        }

        $tarifs = [];

        foreach ($this->tarifRepository->trouverTous() as $tarif) {
            $tarifs[] = TarifDTO::fromEntity($tarif)->toArray();
        }

        return SoireeDTO::fromEntity($soiree, $tarifs);
    }
}