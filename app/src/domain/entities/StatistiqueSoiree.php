<?php
declare(strict_types=1);

namespace nrv\domain\entities;

final readonly class StatistiqueSoiree
{
    public function __construct(
        private int $id,
        private string $nom,
        private int $capacite,
        private int $placesVendues,
        private int $recette
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

    public function getCapacite(): int
    {
        return $this->capacite;
    }

    public function getPlacesVendues(): int
    {
        return $this->placesVendues;
    }

    public function getPlacesDisponibles(): int
    {
        return max(0, $this->capacite - $this->placesVendues);
    }

    /**
     * Renvoi la recette en centimes
     */
    public function getRecette(): int
    {
        return $this->recette;
    }
}