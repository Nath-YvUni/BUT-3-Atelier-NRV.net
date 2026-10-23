<?php
declare(strict_types=1);

namespace nrv\application\dto;

use nrv\domain\entities\Commande;

final readonly class CommandeDTO
{
    public function __construct(
        public int $id,
        public string $reference,
        public string $date_commande,
        public string $statut,
        public string $montant_total
    ) {
    }

    public static function fromEntity(Commande $commande): self
    {
        return new self(
            id: $commande->getId(),
            reference: $commande->getReference(),
            date_commande: $commande->getDateCommande()->format('Y-m-d\TH:i:s'),
            statut: $commande->getStatut(),
            montant_total: Montant::formater($commande->getMontantTotal())
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'date_commande' => $this->date_commande,
            'statut' => $this->statut,
            'montant_total' => $this->montant_total,
            'devise' => 'EUR',
            '_links' => [
                'self' => [
                    'href' => '/api/commandes/' . $this->id,
                    'method' => 'GET',
                ],
                'billets' => [
                    'href' => '/api/commandes/' . $this->id . '/billets',
                    'method' => 'GET',
                ],
            ],
        ];
    }
}
