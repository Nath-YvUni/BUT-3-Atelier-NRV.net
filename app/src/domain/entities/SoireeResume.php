<?php
declare(strict_types=1);

namespace nrv\domain\entities;

use DateTimeImmutable;

final readonly class SoireeResume
{
    public function __construct(
        private int $id,
        private string $nom,
        private DateTimeImmutable $dateHeureDebut,
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

    public function getDateHeureDebut(): DateTimeImmutable
    {
        return $this->dateHeureDebut;
    }
}
