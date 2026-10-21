<?php
declare(strict_types=1);

namespace nrv\domain\entities;

use DateTimeImmutable;

class Soiree {
    public function __construct(
        private int $id,
        private int $festival_id,
        private int $lieu_id,
        private string $nom,
        private string $thematique,
        private DateTimeImmutable $date_heure_debut,
        private int $capacite,
        private int $tarif,
        private string $nom_lieu,
        private array $artistes,
        private array $liste
    )
    { }

    
public function getId(): int
{
    return $this->id;
}

public function getFestivalId(): int
{
    return $this->festival_id;
}

public function getLieuId(): int
{
    return $this->lieu_id;
}

public function getNom(): string
{
    return $this->nom;
}

public function getThematique(): string
{
    return $this->thematique;
}

public function getDate(): DateTimeImmutable
{
    return $this->date_heure_debut;
}

public function getCapacite(): int
{
    return $this->capacite;
}

public function getTarif(): int
{
    return $this->tarif;
}

public function getNomLieu(): string
{
    return $this->nom_lieu;
}

public function getArtistes(): array {
    return $this->artistes;
}


public function getListe(): array
{
    return $this->liste;
}
}