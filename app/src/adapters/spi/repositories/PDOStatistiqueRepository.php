<?php
declare(strict_types=1);

namespace nrv\adapters\spi\repositories;

use nrv\application\exceptions\RepositoryInternalException;
use nrv\application\ports\spi\StatistiqueRepositoryInterface;
use nrv\domain\entities\StatistiqueSoiree;

final class PDOStatistiqueRepository implements StatistiqueRepositoryInterface
{
    public function __construct(private \PDO $pdo)
    {
    }

    public function trouverParSoiree(): array
    {
        $sql = <<<'SQL'
            SELECT
                s."id",
                s."nom",
                s."capacite",
                COALESCE(v."places_vendues", 0) AS places_vendues,
                COALESCE(v."recette", 0) AS recette
            FROM "soiree" s
            LEFT JOIN (
                SELECT
                    b."soiree_id",
                    COUNT(*) AS places_vendues,
                    SUM(b."prix") AS recette
                FROM "billet" b
                JOIN "commande" c ON c."id" = b."commande_id"
                WHERE c."statut" = 'payee'
                GROUP BY b."soiree_id"
            ) v ON v."soiree_id" = s."id"
            ORDER BY s."date_heure_debut", s."id"
            SQL;

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute();

            $lignes = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryInternalException(
                'Erreur lors de la lecture des statistiques.',
                0,
                $e
            );
        }

        return array_map(
            static fn(array $ligne): StatistiqueSoiree =>
                new StatistiqueSoiree(
                    (int) $ligne['id'],
                    (string) $ligne['nom'],
                    (int) $ligne['capacite'],
                    (int) $ligne['places_vendues'],
                    Centimes::depuisDecimal($ligne['recette'])
                ),
            $lignes
        );
    }
}