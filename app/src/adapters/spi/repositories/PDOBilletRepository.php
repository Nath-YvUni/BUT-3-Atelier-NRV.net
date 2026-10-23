<?php
declare(strict_types=1);

namespace nrv\adapters\spi\repositories;

use nrv\application\exceptions\RepositoryInternalException;
use nrv\application\ports\spi\BilletRepositoryInterface;
use nrv\domain\entities\Billet;
use nrv\domain\entities\SoireeResume;
use nrv\domain\entities\Tarif;

final class PDOBilletRepository implements BilletRepositoryInterface
{
    public function __construct(private \PDO $pdo)
    {
    }

    public function trouverParToken(string $token): ?Billet
    {
        try {
            $statement = $this->pdo->prepare(<<<'SQL'
                SELECT
                    b."id",
                    b."reference",
                    b."token",
                    b."prix",
                    u."nom"              AS acheteur,
                    s."id"               AS soiree_id,
                    s."nom"              AS soiree_nom,
                    s."date_heure_debut" AS soiree_date,
                    t."id"               AS tarif_id,
                    t."categorie"        AS tarif_categorie,
                    t."prix"             AS tarif_prix
                FROM "billet" b
                INNER JOIN "commande" c ON c."id" = b."commande_id"
                INNER JOIN "utilisateur" u ON u."id" = c."utilisateur_id"
                INNER JOIN "soiree" s ON s."id" = b."soiree_id"
                INNER JOIN "tarif" t ON t."id" = b."tarif_id"
                WHERE b."token" = :token
                SQL);
            $statement->execute(['token' => $token]);
            $ligne = $statement->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryInternalException(
                'Erreur lors de la lecture du billet.',
                0,
                $e
            );
        }

        if ($ligne === false) {
            return null;
        }

        return new Billet(
            (int) $ligne['id'],
            (string) $ligne['reference'],
            trim((string) $ligne['token']),
            (string) $ligne['acheteur'],
            new SoireeResume(
                (int) $ligne['soiree_id'],
                (string) $ligne['soiree_nom'],
                new \DateTimeImmutable($ligne['soiree_date'])
            ),
            new Tarif(
                (int) $ligne['tarif_id'],
                (string) $ligne['tarif_categorie'],
                Centimes::depuisDecimal($ligne['tarif_prix'])
            ),
            Centimes::depuisDecimal($ligne['prix'])
        );
    }

    public function creerPourCommande(int $commandeId, array $billets): void
    {
        $transactionPropre = !$this->pdo->inTransaction();

        try {
            if ($transactionPropre) {
                $this->pdo->beginTransaction();
            }

            $insertion = $this->pdo->prepare(<<<'SQL'
                INSERT INTO "billet"
                    ("commande_id", "soiree_id", "tarif_id", "pass_id", "reference", "token", "prix")
                VALUES
                    (:commande_id, :soiree_id, :tarif_id, :pass_id, :reference, :token, :prix)
                SQL);

            foreach ($billets as $billet) {
                $insertion->execute([
                    'commande_id' => $commandeId,
                    'soiree_id' => $billet['soiree_id'],
                    'tarif_id' => $billet['tarif_id'],
                    'pass_id' => $billet['pass_id'],
                    'reference' => 'TMP-' . substr($billet['token'], 0, 24),
                    'token' => $billet['token'],
                    'prix' => sprintf('%d.%02d', intdiv($billet['prix'], 100), $billet['prix'] % 100),
                ]);
            }

            $this->pdo->prepare(<<<'SQL'
                UPDATE "billet" b
                SET "reference" = 'BIL-'
                || to_char(c."date_commande", 'YYYY')
                || '-'
                || CASE WHEN b."id" < 10000
                        THEN lpad(b."id"::text, 4, '0')
                        ELSE b."id"::text
                   END
                FROM "commande" c
                WHERE c."id" = b."commande_id"
                  AND b."commande_id" = :commande_id
                  AND b."reference" LIKE 'TMP-%'
                SQL)->execute(['commande_id' => $commandeId]);

            if ($transactionPropre) {
                $this->pdo->commit();
            }
        } catch (\PDOException $e) {
            if ($transactionPropre && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw new RepositoryInternalException(
                'Erreur lors de la création des billets.',
                0,
                $e
            );
        }
    }
}
