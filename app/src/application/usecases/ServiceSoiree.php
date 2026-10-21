<?php
declare(strict_types=1);

namespace nrv\application\usecases;
use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\dto\SoireeDTO;

class ServiceSoiree implements ServiceSoireeInterface 
{

    public function __construct(
        private SoireeRepositoryInterface $soireeRepository
    )
    {}

    public function getAllSoirees(): array
    {
        $listeSoirees = $this->soireeRepository->findByAll();
        return $listeSoirees;
    }

    public function getDetails(int $id) : SoireeDTO
    {
        $soiree = $this->soireeRepository->findById($id);
        if (!$soiree){
            throw new SoireeIntrouvableException();
        }

        return SoireeDTO::fromEntity($soiree);
    }



}