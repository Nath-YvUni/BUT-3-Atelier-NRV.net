<?php
declare(strict_types=1);

namespace nrv\domain\entities;

final readonly class Lieu
{
    public function __construct(
        private int $id,
        private string $nom,
        private string $adresse,
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

    public function getAdresse(): string
    {
        return $this->adresse;
    }
}
