<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

interface ServiceSpectacleInterface
{
    /**
     * Liste des spectacles prête pour la sérialisation JSON (F1).
     *
     * @return array<int, array{id:int, titre:string, date:string, horaire:string, image:?string}>
     */
    public function getSpectacles(): array;
}
