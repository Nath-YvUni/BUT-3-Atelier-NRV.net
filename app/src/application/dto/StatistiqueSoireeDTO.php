<?php
declare(strict_types=1);

namespace nrv\application\dto;

use nrv\domain\entities\StatistiqueSoiree;

final readonly class StatistiqueSoireeDTO
{
    private function __construct(
        private array $donnees
    ) {
    }

    public static function fromEntity(StatistiqueSoiree $statistique): self
    {
        return new self([
            'id' => $statistique->getId(),
            'nom' => $statistique->getNom(),
            'capacite' => $statistique->getCapacite(),
            'places_vendues' => $statistique->getPlacesVendues(),
            'places_disponibles' => $statistique->getPlacesDisponibles(),
            'recette' => Montant::formater($statistique->getRecette()),
        ]);
    }

    public function toArray(): array
    {
        return $this->donnees;
    }
}