<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

interface ServicePanierInterface
{
    public function consulterPanier(int $utilisateurId): array;
    public function ajouterBillets(int $utilisateurId, array $donnees): array;
    public function modifierQuantite(int $utilisateurId, int $ligneId, array $donnees): array;

    public function ajouterPass(int $utilisateurId, array $donnees): array;
}
