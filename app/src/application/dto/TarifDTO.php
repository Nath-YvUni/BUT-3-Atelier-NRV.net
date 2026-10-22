<?php
declare(strict_types=1);

namespace nrv\application\dto;

use nrv\domain\entities\Tarif;

final readonly class TarifDTO
{
    public function __construct(
        public int $id,
        public string $categorie,
        public string $prix
    ) {
    }

    public static function fromEntity(Tarif $tarif): self
    {
        return new self(
            id: $tarif->getId(),
            categorie: $tarif->getCategorie(),
            prix: Montant::formater($tarif->getPrix())
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'categorie' => $this->categorie,
            'prix' => $this->prix,
        ];
    }
}
