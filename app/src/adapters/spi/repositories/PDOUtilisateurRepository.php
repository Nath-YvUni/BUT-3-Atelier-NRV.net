<?php
declare(strict_types=1);

namespace nrv\adapters\spi\repositories;

use nrv\application\exceptions\EmailDejaUtiliseException;
use nrv\application\exceptions\RepositoryInternalException;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use nrv\domain\entities\Utilisateur;

final class PDOUtilisateurRepository implements UtilisateurRepositoryInterface
{
    public function __construct(private \PDO $pdo)
    {
    }

    public function trouverParEmail(string $email): ?Utilisateur
    {
        $sql = <<<'SQL'
            SELECT
                "id",
                "nom",
                "email",
                "mot_de_passe_hash",
                "role"
            FROM "utilisateur"
            WHERE LOWER("email") = :email
            SQL;

        return $this->executer($sql, [
            'email' => strtolower(trim($email)),
        ]);
    }

    public function trouverParId(int $id): ?Utilisateur
    {
        $sql = <<<'SQL'
            SELECT
                "id",
                "nom",
                "email",
                "mot_de_passe_hash",
                "role"
            FROM "utilisateur"
            WHERE "id" = :id
            SQL;

        return $this->executer($sql, [
            'id' => $id,
        ]);
    }

    public function creer(
        string $nom,
        string $email,
        string $motDePasseHash
    ): Utilisateur {
        $sql = <<<'SQL'
            INSERT INTO "utilisateur"
                ("nom", "email", "mot_de_passe_hash", "role")
            VALUES
                (:nom, :email, :mot_de_passe_hash, 'utilisateur')
            ON CONFLICT ("email") DO NOTHING
            RETURNING
                "id",
                "nom",
                "email",
                "mot_de_passe_hash",
                "role"
            SQL;

        $utilisateur = $this->executer($sql, [
            'nom' => $nom,
            'email' => strtolower(trim($email)),
            'mot_de_passe_hash' => $motDePasseHash,
        ]);

        if ($utilisateur === null) {
            throw new EmailDejaUtiliseException();
        }

        return $utilisateur;
    }

    private function executer(
        string $sql,
        array $parametres
    ): ?Utilisateur {
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($parametres);

            $ligne = $statement->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new RepositoryInternalException(
                'Erreur lors de l’accès aux utilisateurs.',
                0,
                $e
            );
        }

        if ($ligne === false) {
            return null;
        }

        return new Utilisateur(
            (int) $ligne['id'],
            (string) $ligne['nom'],
            (string) $ligne['email'],
            (string) $ligne['mot_de_passe_hash'],
            (string) $ligne['role'],
        );
    }
}