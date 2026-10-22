<?php
declare(strict_types=1);

namespace nrv\domain\entities;

final readonly class Tarif
{
    public function __construct(
        private int $id,
        private string $categorie,
        private int $prix,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCategorie(): string
    {
        return $this->categorie;
    }

    public function getPrix(): int
    {
        return $this->prix;
    }
}
