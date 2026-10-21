<?php
declare(strict_types=1);

namespace nrv\adapters\persistance;

use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\domain\entities\Soiree;
use Override;

class SoireeRepository implements SoireeRepositoryInterface{

public function __construct(private \PDO $pdo) {}

 public function findByAll(): array {
    // TODO
    throw new \Exception('Pas encore fait');
 }

 #[Override]
public function findById(int $id): ?Soiree
{
    try {
        $stmt = $this->pdo->prepare(
            'SELECT s.id, s.festival_id, s.lieu_id, s.nom, s.thematique,
                    s.date_heure_debut, s.capacite, l.nom AS lieu_nom
             FROM soiree s
             INNER JOIN lieu l ON l.id = s.lieu_id
             WHERE s.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $stmtTarif = $this->pdo->prepare('SELECT prix FROM tarif WHERE id = 1');
        $stmtTarif->execute();
        $rowTarif = $stmtTarif->fetch(\PDO::FETCH_ASSOC);

        $stmtListe = $this->pdo->prepare(
            'SELECT sp.id, sp.titre, sp.description, sp.url_video, 
            STRING_AGG(DISTINCT a.nom, \', \' ORDER BY a.nom) AS artistes, STRING_AGG(DISTINCT sm.libelle, \', \' ORDER BY sm.libelle) AS styles
            FROM spectacle sp
            LEFT JOIN spectacle_artiste sa ON sa.spectacle_id = sp.id
            LEFT JOIN artiste a ON a.id = sa.artiste_id
            LEFT JOIN spectacle_style ss ON ss.spectacle_id = sp.id
            LEFT JOIN style_musical sm ON ss.style_id = sm.id
            WHERE sp.soiree_id = :id
            GROUP BY sp.id, sp.titre, sp.description, sp.url_video
            ORDER BY sp.id'
        );
        $stmtListe->execute(['id' => $id]);
        $spectacles = $stmtListe->fetchAll(\PDO::FETCH_ASSOC);

        $stmtArtistes = $this->pdo->prepare(
            'SELECT nom
            FROM artiste a
            INNER JOIN spectacle_artiste sa ON a.id = sa.artiste_id
            INNER JOIN spectacle sp ON sp.id = sa.spectacle_id
            WHERE soiree_id = :id'
        );

        $stmtArtistes->execute(['id' => $id]);
        $artistes = $stmtArtistes->fetchAll(\PDO::FETCH_ASSOC);
   } catch (\PDOException $e) {
    throw new \RuntimeException("Erreur BDD : " . $e->getMessage(), previous: $e);
}
    return new Soiree(
        $row['id'],
        $row['festival_id'],
        $row['lieu_id'],
        $row['nom'],
        $row['thematique'],
        new \DateTimeImmutable($row['date_heure_debut']),
        $row['capacite'],
        (int) $rowTarif['prix'],
        $row['lieu_nom'],
        $artistes,
        $spectacles
    );
}
}