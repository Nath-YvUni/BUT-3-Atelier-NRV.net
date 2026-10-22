<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

interface ServicePanierInterface
{
    public function ajouterBillets(int $utilisateurId, array $donnees): array;

    public function ajouterPass(int $utilisateurId, array $donnees): array;
}
