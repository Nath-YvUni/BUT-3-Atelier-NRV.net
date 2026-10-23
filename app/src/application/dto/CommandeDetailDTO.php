<?php
declare(strict_types=1);

namespace nrv\application\dto;

use nrv\domain\entities\Commande;
use nrv\domain\entities\LignePanier;
use nrv\domain\entities\Panier;
use nrv\domain\entities\PassTroisJours;
use nrv\domain\entities\SoireeResume;

final readonly class CommandeDetailDTO
{
    private function __construct(
        private array $donnees
    ) {
    }

    public static function fromEntities(Commande $commande, Panier $panier): self
    {
        return new self([
            'id' => $commande->getId(),
            'reference' => $commande->getReference(),
            'date_commande' => $commande->getDateCommande()->format('Y-m-d\TH:i:s'),
            'statut' => $commande->getStatut(),
            'montant_total' => Montant::formater($commande->getMontantTotal()),
            'devise' => 'EUR',
            'contenu' => [
                'lignes' => array_map(self::ligne(...), $panier->getLignes()),
                'pass' => array_map(self::pass(...), $panier->getPass()),
            ],
        ]);
    }

    public function toArray(): array
    {
        return $this->donnees;
    }

    private static function ligne(LignePanier $ligne): array
    {
        $soiree = $ligne->getSoiree();
        $tarif = $ligne->getTarif();

        return [
            'soiree_id' => $soiree->getId(),
            'nom_soiree' => $soiree->getNom(),
            'date_heure_debut' => $soiree->getDateHeureDebut()->format('Y-m-d\TH:i:s'),
            'tarif_id' => $tarif->getId(),
            'categorie' => $tarif->getCategorie(),
            'prix_unitaire' => Montant::formater($tarif->getPrix()),
            'quantite' => $ligne->getQuantite(),
            'sous_total' => Montant::formater($ligne->sousTotal()),
        ];
    }

    private static function pass(PassTroisJours $pass): array
    {
        $tarif = $pass->getTarif();

        return [
            'id' => $pass->getId(),
            'soiree_ids' => array_map(
                fn(SoireeResume $soiree): int => $soiree->getId(),
                $pass->getSoirees()
            ),
            'tarif_id' => $tarif->getId(),
            'categorie' => $tarif->getCategorie(),
            'prix_unitaire_avant_remise' => Montant::formater($tarif->getPrix()),
            'taux_remise' => Montant::formater($pass->getTauxRemise()),
            'sous_total' => Montant::formater($pass->sousTotal()),
        ];
    }
}
