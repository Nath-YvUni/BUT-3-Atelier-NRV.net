<?php
declare(strict_types=1);

namespace nrv\domain\entities;

use DateTimeImmutable;

final class Commande
{
    public function __construct(
        private int $id,
        private int $utilisateur_id,
        private ?int $panier_id,
        private string $reference,
        private DateTimeImmutable $date_commande,
        private string $statut,
        private int $montant_total,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUtilisateurId(): int
    {
        return $this->utilisateur_id;
    }

    public function getPanierId(): ?int
    {
        return $this->panier_id;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getDateCommande(): DateTimeImmutable
    {
        return $this->date_commande;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getMontantTotal(): int
    {
        return $this->montant_total;
    }
}