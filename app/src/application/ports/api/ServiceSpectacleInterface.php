<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

interface ServiceSpectacleInterface
{
    /**
     * Liste des spectacles, éventuellement filtrés par date.
     *
     * @return array<int, array{id:int, titre:string, date:string, horaire:string, image:?string}>
     */
    public function getSpectacles(?string $date = null): array;
}