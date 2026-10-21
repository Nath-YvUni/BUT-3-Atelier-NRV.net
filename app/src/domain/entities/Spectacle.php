<?php
declare(strict_types=1);

namespace nrv\domain\entities;

final class Spectacle
{
    public function __construct(
        private int $id,
        private int $soiree_id,
        private string $titre,
        private \DateTimeImmutable $date,
        private \DateTimeImmutable $horaire,
        private ?string $image,
        private array $styles = []
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSoireeId(): int
    {
        return $this->soiree_id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getHoraire(): \DateTimeImmutable
    {
        return $this->horaire;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getStyles(): array
    {
        return $this->styles;
    }
}
