<?php
declare(strict_types=1);

namespace nrv\application\dto;

use nrv\domain\entities\SoireeProgramme;

final class SoireeProgrammeDTO
{
    private function __construct(private array $donnees)
    {
    }

    public static function fromEntity(SoireeProgramme $soiree): self
    {
        $lieu = $soiree->getLieu();

        return new self([
            'id' => $soiree->getId(),
            'nom' => $soiree->getNom(),
            'thematique' => $soiree->getThematique(),
            'date_heure_debut' => $soiree->getDateHeureDebut()->format('Y-m-d\TH:i:s'),
            'lieu' => [
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom(),
                'adresse' => $lieu->getAdresse(),
            ],
            'capacite' => $soiree->getCapacite(),
            'places_vendues' => $soiree->getPlacesVendues(),
            'places_disponibles' => $soiree->placesDisponibles(),
            'complete' => $soiree->estComplete(),
            '_links' => [
                'self' => [
                    'href' => '/api/soirees/' . $soiree->getId(),
                    'method' => 'GET',
                ],
            ],
        ]);
    }

    public function toArray(): array
    {
        return $this->donnees;
    }
}
