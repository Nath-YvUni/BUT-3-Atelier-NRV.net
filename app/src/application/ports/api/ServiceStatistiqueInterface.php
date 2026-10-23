<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

interface ServiceStatistiqueInterface
{
    /**
     * Renvoie les stats par soirée + totaux.
     */
    public function getStatistiques(): array;
}