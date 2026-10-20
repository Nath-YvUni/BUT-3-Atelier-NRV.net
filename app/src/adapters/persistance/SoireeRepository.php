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
                $stmt = $this->pdo->prepare('SELECT id, festival_id, lieu_id, nom, thematique, date_heure_debut, capacite FROM soiree WHERE id = :id');
                $stmt->execute(['id' => $id]);
            } catch (\PDOException $e) {
                throw new \RuntimeException("Erreur d'accès à la base de données.", previous: $e);
            }

            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row === false) {
                return null;
            }

           return new Soiree(
                $row['id'],
                $row['festival_id'],
                $row['lieu_id'],
                $row['nom'],
                $row['thematique'],
                new \DateTimeImmutable($row['date_heure_debut']),
                $row['capacite'],
            );
        }
}