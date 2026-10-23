<?php
declare(strict_types=1);

namespace nrv\domain\entities;

final readonly class Billet
{
    public function __construct(
        private int $id,
        private string $reference,
        private string $token,
        private string $acheteur,
        private SoireeResume $soiree,
        private Tarif $tarif,
        private int $prix,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getAcheteur(): string
    {
        return $this->acheteur;
    }

    public function getSoiree(): SoireeResume
    {
        return $this->soiree;
    }

    public function getTarif(): Tarif
    {
        return $this->tarif;
    }

    public function getPrix(): int
    {
        return $this->prix;
    }
}
