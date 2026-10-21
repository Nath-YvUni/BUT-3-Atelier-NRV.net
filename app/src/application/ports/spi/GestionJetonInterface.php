<?php
declare(strict_types=1);

namespace nrv\application\ports\spi;

interface GestionJetonInterface
{
    /**
     * @return array{
     *     access_token:string,
     *     token_type:string,
     *     expires_in:int
     * }
     */
    public function creer(int $utilisateurId): array;

    public function lire(string $jeton): int;
}