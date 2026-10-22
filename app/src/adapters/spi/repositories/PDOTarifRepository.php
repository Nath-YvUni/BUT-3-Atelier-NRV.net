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
        $lignes = $this->executer(
            'SELECT "id", "categorie", "prix" FROM "tarif" WHERE "id" = :id',
            ['id' => $id]
        );

        return $lignes === [] ? null : $this->tarif($lignes[0]);
    }

    public function trouverTous(): array
    {
        $lignes = $this->executer(
            'SELECT "id", "categorie", "prix" FROM "tarif" ORDER BY "id"',
            []
        );

        return array_map($this->tarif(...), $lignes);
    }

    private function executer(string $sql, array $parametres): array
    {
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($parametres);

            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryInternalException(
                'Erreur lors de l’accès aux tarifs.',
                0,
                $e
            );
        }
    }

    private function tarif(array $ligne): Tarif
    {
        return new Tarif(
            (int) $ligne['id'],
            (string) $ligne['categorie'],
            Centimes::depuisDecimal($ligne['prix'])
        );
    }
}
