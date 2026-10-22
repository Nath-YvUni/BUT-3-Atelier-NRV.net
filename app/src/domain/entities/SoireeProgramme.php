<?php
declare(strict_types=1);

namespace nrv\domain\entities;

final readonly class SoireeProgramme
{
    public function __construct(
        private int $id,
        private string $nom,
        private string $thematique,
        private \DateTimeImmutable $dateHeureDebut,
        private Lieu $lieu,
        private int $capacite,
        private int $placesVendues,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getThematique(): string
    {
        return $this->thematique;
    }

    public function getDateHeureDebut(): \DateTimeImmutable
    {
        return $this->dateHeureDebut;
    }

    public function getLieu(): Lieu
    {
        return $this->lieu;
    }

    public function getCapacite(): int
    {
        return $this->capacite;
    }

    public function getPlacesVendues(): int
    {
        return $this->placesVendues;
    }

    public function placesDisponibles(): int
    {
        return max(0, $this->capacite - $this->placesVendues);
    }

    public function estComplete(): bool
    {
        return $this->placesDisponibles() === 0;
    }
}
