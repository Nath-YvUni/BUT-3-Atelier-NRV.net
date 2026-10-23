<?php
declare(strict_types=1);

namespace nrv\application\dto;

use nrv\domain\entities\Billet;

final readonly class BilletDTO
{
    private function __construct(
        private array $donnees
    ) {
    }

    public static function fromEntity(Billet $billet): self
    {
        $soiree = $billet->getSoiree();
        $debut = $soiree->getDateHeureDebut();

        return new self([
            'reference' => $billet->getReference(),
            'token' => $billet->getToken(),
            'acheteur' => $billet->getAcheteur(),
            'soiree' => [
                'id' => $soiree->getId(),
                'nom' => $soiree->getNom(),
                'date' => $debut->format('Y-m-d'),
                'horaire' => $debut->format('H:i'),
            ],
            'tarif' => [
                'categorie' => $billet->getTarif()->getCategorie(),
            ],
            'prix' => Montant::formater($billet->getPrix()),
            'devise' => 'EUR',
        ]);
    }

    public function toArray(): array
    {
        return $this->donnees;
    }
}
