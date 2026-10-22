<?php
declare(strict_types=1);


namespace nrv\application\ports\spi;

use nrv\domain\entities\Soiree;

interface SoireeRepositoryInterface 
{
    public function findByAll() : array;

    public function findById(int $id) : ?Soiree;

    public function existe(int $id) : bool;

}