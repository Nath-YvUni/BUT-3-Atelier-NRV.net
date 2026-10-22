<?php
declare(strict_types=1);

namespace nrv\application\dto;

use nrv\domain\entities\Soiree;

final readonly class SoireeDTO
{
    public function __construct(
        public int $id,
        public int $festival_id,
        public int $lieu_id,
        public string $nom,
        public string $thematique,
        public string $date_heure_debut,
        public int $capacite,
        private array $tarifs,
        private string $nom_lieu,
        private array $artistes,
        private array $liste
    ) {
    }

    public static function fromEntity(Soiree $soiree, array $tarifs): self
    {
        return new self(
            id: $soiree->getId(),
            festival_id: $soiree->getFestivalId(),
            lieu_id: $soiree->getLieuId(),
            nom: $soiree->getNom(),
            thematique: $soiree->getThematique(),
            date_heure_debut: $soiree->getDate()->format('Y-m-d H:i'),
            capacite: $soiree->getCapacite(),
            tarifs: $tarifs,
            nom_lieu: $soiree->getNomLieu(),
            artistes: $soiree->getArtistes(),
            liste: $soiree->getListe()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'festival_id' => $this->festival_id,
            'lieu_id' => $this->lieu_id,
            'nom' => $this->nom,
            'thematique' => $this->thematique,
            'date_heure_debut' => $this->date_heure_debut,
            'capacite' => $this->capacite,
            'tarifs' => $this->tarifs,
            'nom_lieu' => $this->nom_lieu,
            'artistes' => $this->artistes,
            'liste' => $this->liste
        ];
    }
}