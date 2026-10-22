<?php
declare(strict_types=1);

namespace nrv\application\dto;

use nrv\domain\entities\Spectacle;

final readonly class SpectacleDTO
{
    public function __construct(
        public int $id,
        public int $soiree_id,
        public string $titre,
        public string $date,
        public string $horaire,
        public ?string $image,
        public array $styles
    ) {
    }

    public static function fromEntity(Spectacle $spectacle): self
    {
        return new self(
            id: $spectacle->getId(),
            soiree_id: $spectacle->getSoireeId(),
            titre: $spectacle->getTitre(),
            date: $spectacle->getDate()->format('Y-m-d'),
            horaire: $spectacle->getHoraire()->format('H:i'),
            image: $spectacle->getImage(),
            styles: $spectacle->getStyles()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'soiree_id' => $this->soiree_id,
            'titre' => $this->titre,
            'date' => $this->date,
            'horaire' => $this->horaire,
            'image' => $this->image,
            'styles' => $this->styles
        ];
    }
}
