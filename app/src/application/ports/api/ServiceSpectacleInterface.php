<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

interface ServiceSpectacleInterface
{
    /**
     * Liste les spectacles, filtrés éventuellement par date et identifiant de style.
     *
     * @return array<int, array{
     *     id:int,
     *     soiree_id:int,
     *     titre:string,
     *     date:string,
     *     horaire:string,
     *     image:?string,
     *     styles:array<int, array{id:int, libelle:string}>
     * }>
     */
    public function getSpectacles(?string $date = null, ?string $styleId = null): array;
}