<?php
declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\StatistiqueSoiree;

interface StatistiqueRepositoryInterface
{
    /**
     * Renvoie toutes les soirées avec leurs ventes payées.
     *
     * @return StatistiqueSoiree[]
     */
    public function trouverParSoiree(): array;
}