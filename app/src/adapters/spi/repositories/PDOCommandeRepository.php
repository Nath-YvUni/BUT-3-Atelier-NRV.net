<?php
declare(strict_types=1);

namespace nrv\adapters\spi\repositories;

use nrv\application\exceptions\RepositoryInternalException;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\domain\entities\Commande;
use nrv\application\exceptions\PanierVideException;
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

private function executer(string $sql, array $parametres): array
{
    $statement = $this->pdo->prepare($sql);
    $statement->execute($parametres);

    return $statement->fetchAll(\PDO::FETCH_ASSOC);
}
}