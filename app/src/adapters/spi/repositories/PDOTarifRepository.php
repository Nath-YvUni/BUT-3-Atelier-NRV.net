<?php
declare(strict_types=1);

namespace nrv\adapters\spi\repositories;

use nrv\application\exceptions\RepositoryInternalException;
use nrv\application\ports\spi\TarifRepositoryInterface;
use nrv\domain\entities\Tarif;

final class PDOTarifRepository implements TarifRepositoryInterface
{
    public function __construct(private \PDO $pdo)
    {
    }

    public function trouverParId(int $id): ?Tarif
    {
        try {
            $statement = $this->pdo->prepare(
                'SELECT "id", "categorie", "prix" FROM "tarif" WHERE "id" = :id'
            );
            $statement->execute(['id' => $id]);

            $ligne = $statement->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryInternalException(
                'Erreur lors de l’accès aux tarifs.',
                0,
                $e
            );
        }

        if ($ligne === false) {
            return null;
        }

        return new Tarif(
            (int) $ligne['id'],
            (string) $ligne['categorie'],
            Centimes::depuisDecimal($ligne['prix'])
        );
    }
}
