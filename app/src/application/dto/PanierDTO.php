<?php
declare(strict_types=1);

namespace nrv\application\dto;

use nrv\domain\entities\LignePanier;
use nrv\domain\entities\Panier;
use nrv\domain\entities\PassTroisJours;
use nrv\domain\entities\SoireeResume;
use nrv\domain\entities\Tarif;

final readonly class PanierDTO
{
    private function __construct(
        private array $donnees
    ) {
    }

    public static function fromEntity(Panier $panier): self
    {
        return new self([
            'id' => $panier->getId(),
            'lignes' => array_map(self::ligne(...), $panier->getLignes()),
            'pass' => array_map(self::pass(...), $panier->getPass()),
            'nombre_billets' => $panier->nombreBillets(),
            'montant_total' => Montant::formater($panier->montantTotal()),
            'devise' => 'EUR',
            'commande_en_attente_id' => $panier->getCommandeEnAttenteId(),
            'modifiable' => $panier->estModifiable(),
        ]);
    }

    public function toArray(): array
    {
        return $this->donnees;
    }

    private static function ligne(LignePanier $ligne): array
    {
        return [
            'id' => $ligne->getId(),
            'soiree' => self::soiree($ligne->getSoiree()),
            'tarif' => self::tarif($ligne->getTarif()),
            'quantite' => $ligne->getQuantite(),
            'sous_total' => Montant::formater($ligne->sousTotal()),
        ];
    }

    private static function pass(PassTroisJours $pass): array
    {
        return [
            'id' => $pass->getId(),
            'tarif' => self::tarif($pass->getTarif()),
            'taux_remise' => Montant::formater($pass->getTauxRemise()),
            'soirees' => array_map(self::soiree(...), $pass->getSoirees()),
            'montant_avant_remise' => Montant::formater($pass->montantAvantRemise()),
            'montant_remise' => Montant::formater($pass->montantRemise()),
            'sous_total' => Montant::formater($pass->sousTotal()),
        ];
    }

    private static function soiree(SoireeResume $soiree): array
    {
        return [
            'id' => $soiree->getId(),
            'nom' => $soiree->getNom(),
            'date_heure_debut' => $soiree->getDateHeureDebut()->format('Y-m-d\TH:i:s'),
        ];
    }

    private static function tarif(Tarif $tarif): array
    {
        return TarifDTO::fromEntity($tarif)->toArray();
    }
}
