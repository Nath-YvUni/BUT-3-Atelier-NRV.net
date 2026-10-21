<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

interface ServiceSpectacleInterface
{
    /**
     * Liste des spectacles, éventuellement filtrés par date et/ou par style de musique.
     *
     * @return array<int, array{id:int, titre:string, date:string, horaire:string, image:?string}>
     */
    public function getSpectacles(?string $date = null, ?string $style = null): array;
}