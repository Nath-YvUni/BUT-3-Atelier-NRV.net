<?php
declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\dto\PanierDTO;
use nrv\application\exceptions\PanierVerrouilleException;
use nrv\application\exceptions\ValidationException;
use nrv\application\ports\api\ServicePanierInterface;
use nrv\application\ports\spi\PanierRepositoryInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\ports\spi\TarifRepositoryInterface;

final class ServicePanier implements ServicePanierInterface
{
    public function __construct(
        private PanierRepositoryInterface $paniers,
        private SoireeRepositoryInterface $soirees,
        private TarifRepositoryInterface $tarifs,
    ) {
    }

    public function consulterPanier(int $utilisateurId): array
    {
        $panier = $this->paniers->trouverParUtilisateur($utilisateurId);

        return $panier === null
            ? PanierDTO::vide()->toArray()
            : PanierDTO::fromEntity($panier)->toArray();
    }

    public function ajouterBillets(int $utilisateurId, array $donnees): array
    {
        $donnees = $this->validerAjout($donnees);

        $soireeId = $donnees['soiree_id'];
        $tarifId = $donnees['tarif_id'];

        $erreurs = [];

        if (!$this->soirees->existe($soireeId)) {
            $erreurs['soiree_id'] = 'Cette soirée n’existe pas.';
        }

        if ($this->tarifs->trouverParId($tarifId) === null) {
            $erreurs['tarif_id'] = 'Ce tarif n’existe pas.';
        }

        if ($erreurs !== []) {
            throw new ValidationException($erreurs, 'Données invalides.');
        }

        $panier = $this->paniers->trouverParUtilisateur($utilisateurId);

        if ($panier !== null && !$panier->estModifiable()) {
            throw new PanierVerrouilleException();
        }

        $cree = $panier === null || !$panier->contientLigne($soireeId, $tarifId);

        $this->paniers->ajouterBillets(
            $utilisateurId,
            $soireeId,
            $tarifId,
            $donnees['quantite']
        );

        $panier = $this->paniers->trouverParUtilisateur($utilisateurId);

        return [
            'cree' => $cree,
            'panier' => PanierDTO::fromEntity($panier)->toArray(),
        ];
    }

    private function validerAjout(array $donnees): array
    {
        $champs = ['soiree_id', 'tarif_id', 'quantite'];
        $erreurs = [];

        foreach ($champs as $champ) {
            if (
                !array_key_exists($champ, $donnees)
                || !is_int($donnees[$champ])
                || $donnees[$champ] < 1
            ) {
                $erreurs[$champ] = 'Un entier strictement positif est obligatoire.';
            }
        }

        foreach (array_diff(array_keys($donnees), $champs) as $champ) {
            $erreurs[$champ] = 'Champ non autorisé.';
        }

        if ($erreurs !== []) {
            throw new ValidationException($erreurs, 'Données invalides.');
        }

        return $donnees;
    }

    public function modifierQuantite(int $utilisateurId, int $ligneId, array $donnees): array
    {
        $erreurs = [];

        if (
            !array_key_exists('quantite', $donnees)
            || !is_int($donnees['quantite'])
            || $donnees['quantite'] < 1
        ) {
            $erreurs['quantite'] =
                'Un entier strictement positif est obligatoire.';
        }

        foreach (array_diff(array_keys($donnees), ['quantite']) as $champ) {
            $erreurs[$champ] = 'Champ non autorisé.';
        }

        if ($erreurs !== []) {
            throw new ValidationException($erreurs, 'Données invalides.');
        }

        $panier = $this->paniers->trouverParUtilisateur($utilisateurId);

        if ($panier === null) {
            throw new ValidationException(
                ['panier' => 'Panier introuvable.'],
                'Panier introuvable.'
            );
        }

        if (!$panier->estModifiable()) {
            throw new PanierVerrouilleException();
        }

        $this->paniers->modifierQuantite($utilisateurId, $ligneId, $donnees['quantite']);

        $panier = $this->paniers->trouverParUtilisateur($utilisateurId);

        return PanierDTO::fromEntity($panier)->toArray();
    }
}
