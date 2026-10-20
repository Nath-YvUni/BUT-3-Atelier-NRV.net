<?php
declare(strict_types=1);

namespace nrv\adapters\spi\repositories;

use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\application\exceptions\RepositoryInternalException;
use nrv\domain\entities\Spectacle;

final class PDOSpectacleRepository implements SpectacleRepositoryInterface
{
    public function __construct(private \PDO $pdo)
    {
    }

    public function getAll(): array
    {
        $sql = <<<'SQL'
            SELECT
                sp."id"                     AS id,
                sp."titre"                  AS titre,
                so."date_heure_debut"::date AS date,
                sp."horaire_previsionnel"   AS horaire,
                (
                    SELECT img."url"
                    FROM "spectacle_image" img
                    WHERE img."spectacle_id" = sp."id"
                    ORDER BY img."id"
                    LIMIT 1
                ) AS image
            FROM "spectacle" sp
            JOIN "soiree" so ON so."id" = sp."soiree_id"
            ORDER BY so."date_heure_debut", sp."horaire_previsionnel", sp."id"
            SQL;

        try {
            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryInternalException(
                'Erreur lors de la récupération des spectacles',
                (int) $e->getCode(),
                $e
            );
        }

        return array_map(
            static fn (array $row): Spectacle => new Spectacle(
                (int) $row['id'],
                (string) $row['titre'],
                new \DateTimeImmutable($row['date']),
                new \DateTimeImmutable($row['horaire']),
                $row['image'] !== null ? (string) $row['image'] : null,
            ),
            $rows
        );
    }
}
