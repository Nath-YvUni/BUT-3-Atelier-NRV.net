<?php
declare(strict_types=1);

namespace nrv\application\dto;

use nrv\domain\entities\Commande;

final readonly class CommandeDTO
{
    public function __construct(
        public int $id,
        public int $utilisateur_id,
        public ?int $panier_id,
        public string $reference,
        public string $date_commande,
        public string $statut,
        public float $montant_total
    ) {
    }

    public static function fromEntity(Commande $commande): self
    {
        return new self(
            id: $commande->getId(),
            utilisateur_id: $commande->getUtilisateurId(),
            panier_id: $commande->getPanierId(),
            reference: $commande->getReference(),
            date_commande: $commande->getDateCommande()->format('Y-m-d H:i'),
            statut: $commande->getStatut(),
            montant_total: $commande->getMontantTotal() / 100
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'utilisateur_id' => $this->utilisateur_id,
            'panier_id' => $this->panier_id,
            'reference' => $this->reference,
            'date_commande' => $this->date_commande,
            'statut' => $this->statut,
            'montant_total' => $this->montant_total,
        ];
    }
}