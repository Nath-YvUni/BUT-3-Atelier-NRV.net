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
            'montant_total' => self::decimal($panier->montantTotal()),
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
            'sous_total' => self::decimal($ligne->sousTotal()),
        ];
    }

    private static function pass(PassTroisJours $pass): array
    {
        return [
            'id' => $pass->getId(),
            'tarif' => self::tarif($pass->getTarif()),
            'taux_remise' => self::decimal($pass->getTauxRemise()),
            'soirees' => array_map(self::soiree(...), $pass->getSoirees()),
            'montant_avant_remise' => self::decimal($pass->montantAvantRemise()),
            'montant_remise' => self::decimal($pass->montantRemise()),
            'sous_total' => self::decimal($pass->sousTotal()),
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
        return [
            'id' => $tarif->getId(),
            'categorie' => $tarif->getCategorie(),
            'prix' => self::decimal($tarif->getPrix()),
        ];
    }

    private static function decimal(int $centiemes): string
    {
        return sprintf('%d.%02d', intdiv($centiemes, 100), $centiemes % 100);
    }
}
