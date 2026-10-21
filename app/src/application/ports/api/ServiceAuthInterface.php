<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

interface ServiceAuthInterface
{
    public function inscrire(array $donnees): array;

    public function connecter(array $donnees): array;

    public function profil(int $utilisateurId): array;
}