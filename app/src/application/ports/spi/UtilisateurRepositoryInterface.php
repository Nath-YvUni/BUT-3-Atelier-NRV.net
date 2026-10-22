<?php
declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Utilisateur;

interface UtilisateurRepositoryInterface
{
    public function trouverParEmail(string $email): ?Utilisateur;

    public function trouverParId(int $id): ?Utilisateur;

    public function creer(string $nom, string $email, string $motDePasseHash): Utilisateur;
}