<?php
declare(strict_types=1);


namespace nrv\application\ports\spi;

use nrv\domain\entities\Commande;
use nrv\domain\entities\Panier;

interface CommandeRepositoryInterface
{
    public function findByUser(int $utilisateurId): array;

    public function findById(int $utilisateurId, int $commandeId): ?Commande;

    public function commander(int $utilisateurId, array $donnees): bool;

    public function payer(int $commandeId, Panier $panier, callable $genererBillets): array;

    public function trouverContenuPaye(int $commandeId): array;

    public function compterBillets(int $commandeId): int;

}
