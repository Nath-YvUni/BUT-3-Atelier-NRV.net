<?php
declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\dto\SoireeDTO;
use nrv\application\dto\TarifDTO;
use nrv\application\exceptions\SoireeIntrouvableException;
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

    public function getAllSoirees(): array
    {
        return $this->soireeRepository->findByAll();
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