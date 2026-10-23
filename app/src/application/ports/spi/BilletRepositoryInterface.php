<?php
declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Billet;

interface BilletRepositoryInterface
{
    public function trouverParToken(string $token): ?Billet;

    public function creerPourCommande(int $commandeId, array $billets): void;
}
