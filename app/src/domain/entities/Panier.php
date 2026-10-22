<?php
declare(strict_types=1);

namespace nrv\domain\entities;

final readonly class Panier
{
    public function __construct(
        private int $id,
        private array $lignes,
        private array $pass,
        private ?int $commandeEnAttenteId,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLignes(): array
    {
        return $this->lignes;
    }

    public function getPass(): array
    {
        return $this->pass;
    }

    public function getCommandeEnAttenteId(): ?int
    {
        return $this->commandeEnAttenteId;
    }

    public function estModifiable(): bool
    {
        return $this->commandeEnAttenteId === null;
    }

    public function contientLigne(int $soireeId, int $tarifId): bool
    {
        foreach ($this->lignes as $ligne) {
            if (
                $ligne->getSoiree()->getId() === $soireeId
                && $ligne->getTarif()->getId() === $tarifId
            ) {
                return true;
            }
        }

        return false;
    }

    public function nombreBillets(): int
    {
        $nombre = 0;

        foreach ($this->lignes as $ligne) {
            $nombre += $ligne->getQuantite();
        }

        foreach ($this->pass as $pass) {
            $nombre += $pass->nombreBillets();
        }

        return $nombre;
    }

    public function montantTotal(): int
    {
        $total = 0;

        foreach ($this->lignes as $ligne) {
            $total += $ligne->sousTotal();
        }

        foreach ($this->pass as $pass) {
            $total += $pass->sousTotal();
        }

        return $total;
    }
}
