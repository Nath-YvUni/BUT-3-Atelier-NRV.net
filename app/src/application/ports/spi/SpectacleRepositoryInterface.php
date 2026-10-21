<?php
declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Spectacle;

interface SpectacleRepositoryInterface
{
    /**
     * Retourne les spectacles, éventuellement filtrés par date de soirée.
     *
     * @return Spectacle[]
     */
    public function getAll(?\DateTimeImmutable $date = null): array;
}