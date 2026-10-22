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
use nrv\domain\entities\Panier;
use nrv\domain\entities\PassTroisJours;

final class ServicePanier implements ServicePanierInterface
{
    public function __construct(
        private PanierRepositoryInterface $paniers,
        private SoireeRepositoryInterface $soirees,
        private TarifRepositoryInterface $tarifs,
    ) {
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

        $panier = $this->panierModifiable($utilisateurId);

        $cree = $panier === null || !$panier->contientLigne($soireeId, $tarifId);

        $this->paniers->ajouterBillets(
            $utilisateurId,
            $soireeId,
            $tarifId,
            $donnees['quantite']
        );

        return [
            'cree' => $cree,
            'panier' => $this->panier($utilisateurId),
        ];
    }

    public function ajouterPass(int $utilisateurId, array $donnees): array
    {
        $donnees = $this->validerPass($donnees);

        $erreurs = [];

        $inconnues = array_values(array_filter(
            $donnees['soiree_ids'],
            fn(int $soireeId): bool => !$this->soirees->existe($soireeId)
        ));

        if ($inconnues !== []) {
            $erreurs['soiree_ids'] = 'Ces soirées n’existent pas : ' . implode(', ', $inconnues) . '.';
        }

        if ($this->tarifs->trouverParId($donnees['tarif_id']) === null) {
            $erreurs['tarif_id'] = 'Ce tarif n’existe pas.';
        }

        if ($erreurs !== []) {
            throw new ValidationException($erreurs, 'Données invalides.');
        }

        $this->panierModifiable($utilisateurId);

        $this->paniers->ajouterPass(
            $utilisateurId,
            $donnees['tarif_id'],
            $donnees['soiree_ids'],
            PassTroisJours::TAUX_REMISE
        );

        return $this->panier($utilisateurId);
    }

    private function panierModifiable(int $utilisateurId): ?Panier
    {
        $panier = $this->paniers->trouverParUtilisateur($utilisateurId);

        if ($panier !== null && !$panier->estModifiable()) {
            throw new PanierVerrouilleException();
        }

        return $panier;
    }

    private function panier(int $utilisateurId): array
    {
        return PanierDTO::fromEntity(
            $this->paniers->trouverParUtilisateur($utilisateurId)
        )->toArray();
    }

    private function validerAjout(array $donnees): array
    {
        $champs = ['soiree_id', 'tarif_id', 'quantite'];
        $erreurs = [];

        foreach ($champs as $champ) {
            if (!array_key_exists($champ, $donnees) || !$this->estEntierPositif($donnees[$champ])) {
                $erreurs[$champ] = 'Un entier strictement positif est obligatoire.';
            }
        }

        $erreurs += $this->champsNonAutorises($donnees, $champs);

        if ($erreurs !== []) {
            throw new ValidationException($erreurs, 'Données invalides.');
        }

        return $donnees;
    }

    private function validerPass(array $donnees): array
    {
        $erreurs = [];

        $soireeIds = $donnees['soiree_ids'] ?? null;

        if (
            !is_array($soireeIds)
            || count($soireeIds) !== PassTroisJours::NOMBRE_SOIREES
            || array_filter($soireeIds, fn(mixed $id): bool => !$this->estEntierPositif($id)) !== []
        ) {
            $erreurs['soiree_ids'] = 'Une liste de trois identifiants de soirée est obligatoire.';
        } elseif (count(array_unique($soireeIds)) !== count($soireeIds)) {
            $erreurs['soiree_ids'] = 'Les trois soirées doivent être différentes.';
        }

        if (!array_key_exists('tarif_id', $donnees) || !$this->estEntierPositif($donnees['tarif_id'])) {
            $erreurs['tarif_id'] = 'Un entier strictement positif est obligatoire.';
        }

        $erreurs += $this->champsNonAutorises($donnees, ['soiree_ids', 'tarif_id']);

        if ($erreurs !== []) {
            throw new ValidationException($erreurs, 'Données invalides.');
        }

        return $donnees;
    }

    private function estEntierPositif(mixed $valeur): bool
    {
        return is_int($valeur) && $valeur >= 1;
    }

    private function champsNonAutorises(array $donnees, array $champs): array
    {
        $erreurs = [];

        foreach (array_diff(array_keys($donnees), $champs) as $champ) {
            $erreurs[$champ] = 'Champ non autorisé.';
        }

        return $erreurs;
    }
}
