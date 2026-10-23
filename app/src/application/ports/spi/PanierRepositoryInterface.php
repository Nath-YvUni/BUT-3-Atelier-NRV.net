<?php
declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Panier;

interface PanierRepositoryInterface
{
    public function trouverParUtilisateur(int $utilisateurId): ?Panier;

    public function ajouterBillets(
        int $utilisateurId,
        int $soireeId,
        int $tarifId,
        int $quantite
    ): void;

    public function modifierQuantite(int $utilisateurId, int $ligneId, int $quantite): void;
    public function ajouterPass(
        int $utilisateurId,
        int $tarifId,
        array $soireeIds,
        int $tauxRemise
    ): void;
}
