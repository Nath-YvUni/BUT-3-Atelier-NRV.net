<?php
declare(strict_types=1);

namespace nrv\application\dto;

use nrv\domain\entities\Commande;

final readonly class PaiementDTO
{
    private function __construct(
        private array $donnees
    ) {
    }

    public static function fromEntity(Commande $commande, int $nombreBillets): self
    {
        return new self([
            'id' => $commande->getId(),
            'reference' => $commande->getReference(),
            'statut' => $commande->getStatut(),
            'montant_total' => Montant::formater($commande->getMontantTotal()),
            'devise' => 'EUR',
            'nombre_billets' => $nombreBillets,
        ]);
    }

    public function toArray(): array
    {
        return $this->donnees;
    }
}
