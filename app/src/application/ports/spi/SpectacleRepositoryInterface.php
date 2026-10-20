<?php
declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Spectacle;

interface SpectacleRepositoryInterface
{
    /**
     * Retourne la liste de tous les spectacles.
     *
     * @return Spectacle[]
     */
    public function getAll(): array;
}
