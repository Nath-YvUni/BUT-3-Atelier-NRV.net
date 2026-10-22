<?php
declare(strict_types=1);

namespace nrv\domain\entities;

final readonly class Utilisateur
{
    public function __construct(
        private int $id,
        private string $nom,
        private string $email,
        private string $motDePasseHash,
        private string $role,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function verifierMotDePasse(string $motDePasse): bool
    {
        return password_verify($motDePasse, $this->motDePasseHash);
    }
}