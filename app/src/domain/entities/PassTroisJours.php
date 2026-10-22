<?php
declare(strict_types=1);

namespace nrv\domain\entities;

final readonly class PassTroisJours
{
    public const NOMBRE_SOIREES = 3;

    public const TAUX_REMISE = 300;

    public function __construct(
        private int $id,
        private Tarif $tarif,
        private int $tauxRemise,
        private array $soirees,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTarif(): Tarif
    {
        return $this->tarif;
    }

    public function getTauxRemise(): int
    {
        return $this->tauxRemise;
    }

    public function getSoirees(): array
    {
        return $this->soirees;
    }

    public function nombreBillets(): int
    {
        return count($this->soirees);
    }

    public function montantAvantRemise(): int
    {
        return $this->tarif->getPrix() * $this->nombreBillets();
    }

    public function montantRemise(): int
    {
        return intdiv($this->montantAvantRemise() * $this->tauxRemise + 5000, 10000);
    }

    public function sousTotal(): int
    {
        return $this->montantAvantRemise() - $this->montantRemise();
    }
}
