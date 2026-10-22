<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

interface ServicePanierInterface
{
    public function consulterPanier(int $utilisateurId): array;
    public function ajouterBillets(int $utilisateurId, array $donnees): array;
}
