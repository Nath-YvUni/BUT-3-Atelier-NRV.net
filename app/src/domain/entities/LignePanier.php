<?php
declare(strict_types=1);

namespace nrv\domain\entities;

final readonly class LignePanier
{
    public function __construct(
        private int $id,
        private SoireeResume $soiree,
        private Tarif $tarif,
        private int $quantite,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSoiree(): SoireeResume
    {
        return $this->soiree;
    }

    public function getTarif(): Tarif
    {
        return $this->tarif;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function sousTotal(): int
    {
        return $this->tarif->getPrix() * $this->quantite;
    }
}
