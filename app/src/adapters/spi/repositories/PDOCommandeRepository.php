<?php
declare(strict_types=1);

namespace nrv\adapters\spi\repositories;

use nrv\application\exceptions\RepositoryInternalException;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\domain\entities\Commande;
use nrv\application\exceptions\PanierVideException;
use nrv\domain\entities\LignePanier;
use nrv\domain\entities\Panier;
use nrv\domain\entities\PassTroisJours;
use nrv\domain\entities\SoireeResume;
use nrv\domain\entities\Tarif;
use Override;

final class PDOCommandeRepository implements CommandeRepositoryInterface
{
    public function __construct(private \PDO $pdo)
    {
    }

    #[Override]
    public function findByUser(int $utilisateurId): array
    {
        $sql = <<<'SQL'
            SELECT
                c."id",
                c."utilisateur_id",
                c."panier_id",
                c."reference",
                c."date_commande",
                c."statut",
                c."montant_total"
            FROM "commande" c
            WHERE c."utilisateur_id" = :utilisateur_id
            ORDER BY c."date_commande" DESC, c."id" DESC
            SQL;

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute(['utilisateur_id' => $utilisateurId]);
            $lignes = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryInternalException(
                'Erreur lors de la lecture des commandes.',
                0,
                $e
            );
        }

        return array_map(
            fn(array $ligne): Commande => new Commande(
                $ligne['id'],
                $ligne['utilisateur_id'],
                $ligne['panier_id'] !== null ? (int) $ligne['panier_id'] : null,
                $ligne['reference'],
                new \DateTimeImmutable($ligne['date_commande']),
                $ligne['statut'],
                Centimes::depuisDecimal($ligne['montant_total'])
            ),
            $lignes
        );
    }

    #[Override]
    public function findById(int $utilisateurId, int $commandeId): ?Commande
    {
        $sql = <<<'SQL'
        SELECT
            c."id",
            c."utilisateur_id",
            c."panier_id",
            c."reference",
            c."date_commande",
            c."statut",
            c."montant_total"
        FROM "commande" c
        WHERE c."id" = :commande_id
          AND c."utilisateur_id" = :utilisateur_id
        SQL;

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                'commande_id' => $commandeId,
                'utilisateur_id' => $utilisateurId,
            ]);
            $ligne = $statement->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryInternalException(
                'Erreur lors de la lecture de la commande.',
                0,
                $e
            );
        }

        if ($ligne === false) {
            return null;
        }

        return new Commande(
            (int) $ligne['id'],
            (int) $ligne['utilisateur_id'],
            $ligne['panier_id'] !== null ? (int) $ligne['panier_id'] : null,
            (string) $ligne['reference'],
            new \DateTimeImmutable($ligne['date_commande']),
            (string) $ligne['statut'],
            Centimes::depuisDecimal($ligne['montant_total'])
        );
    }

    #[Override]
    public function commander(int $utilisateurId, array $donnees): bool
    {
        try {
            $this->pdo->beginTransaction();

            $panier = $this->executer(
                'SELECT "id" FROM "panier" WHERE "utilisateur_id" = :utilisateur_id FOR UPDATE',
                ['utilisateur_id' => $utilisateurId]
            );

            if ($panier === []) {
                throw new PanierVideException();
            }

            $panierId = (int) $panier[0]['id'];

            $existante = $this->executer(
                'SELECT "id", "statut" FROM "commande" WHERE "panier_id" = :panier_id',
                ['panier_id' => $panierId]
            );

            if ($existante !== []) {
                if ($existante[0]['statut'] === 'en_attente') {
                    $this->pdo->commit();

                    return false;
                }

                $this->executer(
                    'UPDATE "commande" SET "panier_id" = NULL WHERE "id" = :id',
                    ['id' => $existante[0]['id']]
                );
            }

            $contenu = $this->executer(
                <<<'SQL'
                SELECT
                    (SELECT COUNT(*) FROM "ligne_panier" WHERE "panier_id" = :panier_lignes)
                  + (SELECT COUNT(*) FROM "pass_trois_jours" WHERE "panier_id" = :panier_pass) AS n
                SQL,
                ['panier_lignes' => $panierId, 'panier_pass' => $panierId]
            );

            if ((int) $contenu[0]['n'] === 0) {
                throw new PanierVideException();
            }

            $total = $this->executer(
                <<<'SQL'
                SELECT
                    COALESCE((
                        SELECT SUM(lp."quantite" * t."prix")
                        FROM "ligne_panier" lp
                        JOIN "tarif" t ON t."id" = lp."tarif_id"
                        WHERE lp."panier_id" = :panier_lignes
                    ), 0)
                  + COALESCE((
                        SELECT SUM(
                            (SELECT COUNT(*) FROM "pass_soiree" ps WHERE ps."pass_id" = pt."id")
                            * ROUND(t."prix" * (100 - pt."taux_remise") / 100, 2)
                        )
                        FROM "pass_trois_jours" pt
                        JOIN "tarif" t ON t."id" = pt."tarif_id"
                        WHERE pt."panier_id" = :panier_pass
                    ), 0) AS total
                SQL,
                ['panier_lignes' => $panierId, 'panier_pass' => $panierId]
            );

            $this->executer(
                <<<'SQL'
                INSERT INTO "commande" ("utilisateur_id", "panier_id", "reference", "montant_total")
                VALUES (:utilisateur_id, :panier_id, :reference, :montant)
                SQL,
                [
                    'utilisateur_id' => $utilisateurId,
                    'panier_id' => $panierId,
                    'reference' => 'CMD-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4))),
                    'montant' => number_format((float) $total[0]['total'], 2, '.', ''),
                ]
            );

            $this->pdo->commit();

            return true;
        } catch (\PDOException $e) {
            error_log('ERREUR SQL BILLET : ' . $e->getMessage());
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw new RepositoryInternalException('Erreur lors de la création de la commande.', 0, $e);
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }



            throw $e;
        }
    }

    #[Override]
    public function payer(int $commandeId, Panier $panier, callable $genererBillets): array
    {
        $transaction = !$this->pdo->inTransaction();

        try {
            if ($transaction) {
                $this->pdo->beginTransaction();
            }

            $commande = $this->executer(
                'SELECT "statut" FROM "commande" WHERE "id" = :id FOR UPDATE',
                ['id' => $commandeId]
            );

            if ($commande[0]['statut'] !== 'en_attente') {
                if ($transaction) {
                    $this->pdo->commit();
                }

                return [];
            }

            $demandes = $panier->billetsParSoiree();
            $ids = '{' . implode(',', array_keys($demandes)) . '}';

            $this->executer(
                'SELECT "id" FROM "soiree" WHERE "id" = ANY(CAST(:ids AS integer[])) ORDER BY "id" FOR UPDATE',
                ['ids' => $ids]
            );

            $soirees = $this->executer(
                <<<'SQL'
                SELECT s."id", s."nom", s."capacite", COUNT(b."id") AS vendues
                FROM "soiree" s
                LEFT JOIN "billet" b ON b."soiree_id" = s."id"
                WHERE s."id" = ANY(CAST(:ids AS integer[]))
                GROUP BY s."id"
                ORDER BY s."id"
                SQL,
                ['ids' => $ids]
            );

            $manques = [];

            foreach ($soirees as $soiree) {
                $disponibles = max(0, (int) $soiree['capacite'] - (int) $soiree['vendues']);
                $demandees = $demandes[(int) $soiree['id']];

                if ($demandees > $disponibles) {
                    $manques[] = [
                        'soiree_id' => (int) $soiree['id'],
                        'nom' => $soiree['nom'],
                        'quantite_demandee' => $demandees,
                        'places_disponibles' => $disponibles,
                    ];
                }
            }

            if ($manques !== []) {
                $this->changerStatut($commandeId, 'refusee');

                if ($transaction) {
                    $this->pdo->commit();
                }

                return $manques;
            }

            foreach ($panier->getPass() as $pass) {
                $this->executer(
                    'UPDATE "pass_trois_jours" SET "commande_id" = :commande_id WHERE "id" = :id',
                    [
                        'commande_id' => $commandeId,
                        'id' => $pass->getId()
                    ]
                );
            }

            $genererBillets($commandeId, $panier);

            $this->changerStatut($commandeId, 'payee');

            $this->executer(
                'DELETE FROM "panier" WHERE "id" = :id',
                ['id' => $panier->getId()]
            );

            if ($transaction) {
                $this->pdo->commit();
            }

            return [];

        } catch (\Throwable $e) {
            error_log('ERREUR SQL BILLET : ' . $e->getMessage());
            if ($transaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($e instanceof \PDOException) {
                throw new RepositoryInternalException(
                    'Erreur lors du paiement de la commande.',
                    0,
                    $e
                );
            }


            throw $e;
        }
    }

    #[Override]
    public function trouverContenuPaye(int $commandeId): array
    {
        try {
            $lignes = $this->executer(
                <<<'SQL'
                SELECT
                    MIN(b."id")          AS id,
                    COUNT(*)             AS quantite,
                    s."id"               AS soiree_id,
                    s."nom"              AS soiree_nom,
                    s."date_heure_debut" AS soiree_date,
                    t."id"               AS tarif_id,
                    t."categorie"        AS tarif_categorie,
                    b."prix"             AS tarif_prix
                FROM "billet" b
                JOIN "soiree" s ON s."id" = b."soiree_id"
                JOIN "tarif" t ON t."id" = b."tarif_id"
                WHERE b."commande_id" = :commande_id
                  AND b."pass_id" IS NULL
                GROUP BY s."id", t."id", b."prix"
                ORDER BY s."date_heure_debut", t."id"
                SQL,
                ['commande_id' => $commandeId]
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
                WHERE pt."commande_id" = :commande_id
                ORDER BY pt."id"
                SQL,
                ['commande_id' => $commandeId]
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
                WHERE pt."commande_id" = :commande_id
                ORDER BY s."date_heure_debut", s."id"
                SQL,
                ['commande_id' => $commandeId]
            );
        } catch (\PDOException $e) {
            throw new RepositoryInternalException('Erreur lors de la lecture de la commande.', 0, $e);
        }

        $soireesParPass = [];

        foreach ($soireesPass as $ligne) {
            $soireesParPass[(int) $ligne['pass_id']][] = $this->soiree($ligne);
        }

        return [
            'lignes' => array_map(
                fn(array $ligne): LignePanier => new LignePanier(
                    (int) $ligne['id'],
                    $this->soiree($ligne),
                    $this->tarif($ligne),
                    (int) $ligne['quantite']
                ),
                $lignes
            ),
            'pass' => array_map(
                fn(array $ligne): PassTroisJours => new PassTroisJours(
                    (int) $ligne['id'],
                    $this->tarif($ligne),
                    Centimes::depuisDecimal($ligne['taux_remise']),
                    $soireesParPass[(int) $ligne['id']] ?? []
                ),
                $pass
            ),
        ];
    }

    #[Override]
    public function compterBillets(int $commandeId): int
    {
        try {
            $resultat = $this->executer(
                'SELECT COUNT(*) AS nombre FROM "billet" WHERE "commande_id" = :commande_id',
                ['commande_id' => $commandeId]
            );
        } catch (\PDOException $e) {
            throw new RepositoryInternalException('Erreur lors de la lecture des billets.', 0, $e);
        }

        return (int) $resultat[0]['nombre'];
    }

    private function changerStatut(int $commandeId, string $statut): void
    {
        $this->executer(
            'UPDATE "commande" SET "statut" = :statut WHERE "id" = :id',
            ['statut' => $statut, 'id' => $commandeId]
        );
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

    private function executer(string $sql, array $parametres): array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parametres);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}