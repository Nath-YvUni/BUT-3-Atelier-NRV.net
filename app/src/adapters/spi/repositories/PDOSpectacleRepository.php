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

    public function getAll(?\DateTimeImmutable $date = null, ?string $style = null): array
    {
        $sql = <<<'SQL'
            SELECT
                sp."id"                     AS id,
                sp."soiree_id"              AS soiree_id,
                sp."titre"                  AS titre,
                so."date_heure_debut"::date AS date,
                sp."horaire_previsionnel"  AS horaire,
                STRING_AGG(sm."libelle", ', ') AS styles,
                (
                    SELECT img."url"
                    FROM "spectacle_image" img
                    WHERE img."spectacle_id" = sp."id"
                    ORDER BY img."id"
                    LIMIT 1
                ) AS image
            FROM "spectacle" sp
            JOIN "soiree" so ON so."id" = sp."soiree_id"
            LEFT JOIN "spectacle_style" ss ON ss."spectacle_id" = sp."id"
            LEFT JOIN "style_musical" sm ON sm."id" = ss."style_id"
            SQL;

        $parametres = [];
        $conditions = [];

        if ($date !== null) {
            $conditions[] = 'so."date_heure_debut"::date = :date';
            $parametres['date'] = $date->format('Y-m-d');
        }

        if ($style !== null) {
            $conditions[] = 'sp."id" IN (
                SELECT ss_f."spectacle_id" 
                FROM "spectacle_style" ss_f 
                JOIN "style_musical" sm_f ON sm_f."id" = ss_f."style_id" 
                WHERE sm_f."libelle" = :style
            )';
            $parametres['style'] = $style;
        }

        // Ajout des conditions à la requête SQL si elles existent
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' GROUP BY sp."id", sp."soiree_id", sp."titre", so."date_heure_debut", sp."horaire_previsionnel"';

        $sql .= ' ORDER BY so."date_heure_debut",'
            . ' sp."horaire_previsionnel", sp."id"';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($parametres);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryInternalException(
                'Erreur lors de la récupération des spectacles',
                (int) $e->getCode(),
                $e
            );
        }

        return array_map(
            static fn(array $row): Spectacle => new Spectacle(
                (int) $row['id'],
                (int) $row['soiree_id'],
                (string) $row['titre'],
                new \DateTimeImmutable($row['date']),
                new \DateTimeImmutable($row['horaire']),
                $row['image'] !== null ? (string) $row['image'] : null,
                $row['styles'] !== null ? explode(', ', $row['styles']) : []
            ),
            $rows
        );
    }
}