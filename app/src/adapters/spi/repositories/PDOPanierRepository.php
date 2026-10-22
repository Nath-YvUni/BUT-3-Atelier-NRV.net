<?php
declare(strict_types=1);

namespace nrv\adapters\spi\repositories;

use nrv\application\exceptions\RepositoryInternalException;
use nrv\application\ports\spi\PanierRepositoryInterface;
use nrv\domain\entities\LignePanier;
use nrv\domain\entities\Panier;
use nrv\domain\entities\PassTroisJours;
use nrv\domain\entities\SoireeResume;
use nrv\domain\entities\Tarif;

final class PDOPanierRepository implements PanierRepositoryInterface
{
    public function __construct(private \PDO $pdo)
    {
    }

    public function trouverParUtilisateur(int $utilisateurId): ?Panier
    {
        try {
            $panier = $this->executer(
                <<<'SQL'
                    SELECT
                        p."id",
                        c."id" AS commande_en_attente_id
                    FROM "panier" p
                    LEFT JOIN "commande" c
                        ON c."panier_id" = p."id"
                        AND c."statut" = 'en_attente'
                    WHERE p."utilisateur_id" = :utilisateur_id
                    SQL,
                ['utilisateur_id' => $utilisateurId]
            );

            if ($panier === []) {
                return null;
            }

            $panierId = (int) $panier[0]['id'];

            $lignes = $this->executer(
                <<<'SQL'
                    SELECT
                        lp."id",
                        lp."quantite",
                        s."id"               AS soiree_id,
                        s."nom"              AS soiree_nom,
                        s."date_heure_debut" AS soiree_date,
                        t."id"               AS tarif_id,
                        t."categorie"        AS tarif_categorie,
                        t."prix"             AS tarif_prix
                    FROM "ligne_panier" lp
                    JOIN "soiree" s ON s."id" = lp."soiree_id"
                    JOIN "tarif" t ON t."id" = lp."tarif_id"
                    WHERE lp."panier_id" = :panier_id
                    ORDER BY s."date_heure_debut", t."id", lp."id"
                    SQL,
                ['panier_id' => $panierId]
            );

            $pass = $this->executer(
                <<<'SQL'
                    SELECT
                        pt."id",
                        pt."taux_remise",
                        t."id"        AS tarif_id,
                        t."categorie" AS tarif_categorie,
                        t."prix"      AS tarif_prix
                    FROM "pass_trois_jours" pt
                    JOIN "tarif" t ON t."id" = pt."tarif_id"
                    WHERE pt."panier_id" = :panier_id
                    ORDER BY pt."id"
                    SQL,
                ['panier_id' => $panierId]
            );

            $soireesPass = $this->executer(
                <<<'SQL'
                    SELECT
                        ps."pass_id",
                        s."id"               AS soiree_id,
                        s."nom"              AS soiree_nom,
                        s."date_heure_debut" AS soiree_date
                    FROM "pass_soiree" ps
                    JOIN "pass_trois_jours" pt ON pt."id" = ps."pass_id"
                    JOIN "soiree" s ON s."id" = ps."soiree_id"
                    WHERE pt."panier_id" = :panier_id
                    ORDER BY s."date_heure_debut", s."id"
                    SQL,
                ['panier_id' => $panierId]
            );
        } catch (\PDOException $e) {
            throw new RepositoryInternalException(
                'Erreur lors de la lecture du panier.',
                0,
                $e
            );
        }

        $soireesParPass = [];

        foreach ($soireesPass as $ligne) {
            $soireesParPass[(int) $ligne['pass_id']][] = $this->soiree($ligne);
        }

        return new Panier(
            $panierId,
            array_map(
                fn(array $ligne): LignePanier => new LignePanier(
                    (int) $ligne['id'],
                    $this->soiree($ligne),
                    $this->tarif($ligne),
                    (int) $ligne['quantite']
                ),
                $lignes
            ),
            array_map(
                fn(array $ligne): PassTroisJours => new PassTroisJours(
                    (int) $ligne['id'],
                    $this->tarif($ligne),
                    Centimes::depuisDecimal($ligne['taux_remise']),
                    $soireesParPass[(int) $ligne['id']] ?? []
                ),
                $pass
            ),
            $panier[0]['commande_en_attente_id'] !== null
                ? (int) $panier[0]['commande_en_attente_id']
                : null
        );
    }

    public function ajouterBillets(
        int $utilisateurId,
        int $soireeId,
        int $tarifId,
        int $quantite
    ): void {
        try {
            $this->executer(
                <<<'SQL'
                    INSERT INTO "panier" ("utilisateur_id")
                    VALUES (:utilisateur_id)
                    ON CONFLICT ("utilisateur_id") DO NOTHING
                    SQL,
                ['utilisateur_id' => $utilisateurId]
            );

            $this->executer(
                <<<'SQL'
                    INSERT INTO "ligne_panier" ("panier_id", "soiree_id", "tarif_id", "quantite")
                    SELECT p."id", :soiree_id, :tarif_id, :quantite
                    FROM "panier" p
                    WHERE p."utilisateur_id" = :utilisateur_id
                    ON CONFLICT ("panier_id", "soiree_id", "tarif_id")
                    DO UPDATE SET "quantite" = "ligne_panier"."quantite" + EXCLUDED."quantite"
                    SQL,
                [
                    'utilisateur_id' => $utilisateurId,
                    'soiree_id' => $soireeId,
                    'tarif_id' => $tarifId,
                    'quantite' => $quantite,
                ]
            );
        } catch (\PDOException $e) {
            throw new RepositoryInternalException(
                'Erreur lors de l’ajout au panier.',
                0,
                $e
            );
        }
    }

    private function executer(string $sql, array $parametres): array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parametres);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function soiree(array $ligne): SoireeResume
    {
        return new SoireeResume(
            (int) $ligne['soiree_id'],
            (string) $ligne['soiree_nom'],
            new \DateTimeImmutable($ligne['soiree_date'])
        );
    }

    private function tarif(array $ligne): Tarif
    {
        return new Tarif(
            (int) $ligne['tarif_id'],
            (string) $ligne['tarif_categorie'],
            Centimes::depuisDecimal($ligne['tarif_prix'])
        );
    }
}
