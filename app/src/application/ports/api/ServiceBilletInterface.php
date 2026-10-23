<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

use nrv\domain\entities\Panier;

interface ServiceBilletInterface
{
    public function getUserBillets(int $utilisateurId): array;
    /**
     * Génère un billet (avec son token aléatoire) par place du panier,
     * à appeler lorsque la commande est payée.
     */
    public function genererBillets(int $commandeId, Panier $panier): void;

    public function consulterBillet(string $token): array;
}
