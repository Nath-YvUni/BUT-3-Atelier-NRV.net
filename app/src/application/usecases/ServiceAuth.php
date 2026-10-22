<?php
declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\exceptions\AuthentificationException;
use nrv\application\exceptions\EmailDejaUtiliseException;
use nrv\application\exceptions\ValidationException;
use nrv\application\ports\api\ServiceAuthInterface;
use nrv\application\ports\spi\GestionJetonInterface;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use nrv\domain\entities\Utilisateur;

final class ServiceAuth implements ServiceAuthInterface
{
    public function __construct(
        private UtilisateurRepositoryInterface $utilisateurs,
        private GestionJetonInterface $jetons,
    ) {
    }

    public function inscrire(array $donnees): array
    {
        $donnees = $this->valider($donnees, true);

        if ($this->utilisateurs->trouverParEmail($donnees['email']) !== null) {
            throw new EmailDejaUtiliseException();
        }

        $hash = password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT);

        $utilisateur = $this->utilisateurs->creer($donnees['nom'], $donnees['email'], $hash);

        return $this->profilPublic($utilisateur);
    }

    public function connecter(array $donnees): array
    {
        $donnees = $this->valider($donnees, false);

        $utilisateur = $this->utilisateurs->trouverParEmail($donnees['email']);

        if ($utilisateur === null || !$utilisateur->verifierMotDePasse($donnees['mot_de_passe'])) {
            throw new AuthentificationException('Adresse e-mail ou mot de passe incorrect.');
        }

        return [
            ...$this->jetons->creer($utilisateur->getId()),
            'utilisateur' => $this->profilPublic($utilisateur),
        ];
    }

    public function profil(int $utilisateurId): array
    {
        $utilisateur = $this->utilisateurs->trouverParId($utilisateurId);

        if ($utilisateur === null) {
            throw new AuthentificationException('Utilisateur introuvable.');
        }

        return $this->profilPublic($utilisateur);
    }

    private function profilPublic(Utilisateur $utilisateur): array
    {
        return [
            'id' => $utilisateur->getId(),
            'nom' => $utilisateur->getNom(),
            'email' => $utilisateur->getEmail(),
            'role' => $utilisateur->getRole(),
        ];
    }

    private function valider(array $donnees, bool $inscription): array
    {
        $champs = $inscription
            ? ['nom', 'email', 'mot_de_passe']
            : ['email', 'mot_de_passe'];

        $erreurs = [];

        foreach ($champs as $champ) {
            if (!array_key_exists($champ, $donnees) || !is_string($donnees[$champ])) {
                $erreurs[$champ] = 'Une chaîne est obligatoire.';
            }
        }

        foreach (array_diff(array_keys($donnees), $champs) as $champ) {
            $erreurs[$champ] = 'Champ non autorisé.';
        }

        if ($erreurs !== []) {
            throw new ValidationException($erreurs, 'Données invalides.');
        }

        $email = strtolower(trim($donnees['email']));
        $motDePasse = $donnees['mot_de_passe'];

        if (strlen($email) > 255 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $erreurs['email'] = 'Adresse e-mail invalide.';
        }

        if ($motDePasse === '' || str_contains($motDePasse, "\0")) {
            $erreurs['mot_de_passe'] = 'Mot de passe invalide.';
        } elseif ($inscription) {
            if (preg_match('/\A.{12,}\z/us', $motDePasse) !== 1) {
                $erreurs['mot_de_passe'] = 'Le mot de passe doit contenir au moins 12 caractères.';
            } elseif (strlen($motDePasse) > 72) {
                $erreurs['mot_de_passe'] = 'Le mot de passe dépasse la limite de 72 octets.';
            }
        }

        if ($inscription) {
            $nom = trim($donnees['nom']);

            if (str_contains($nom, "\0") || preg_match('/\A.{1,100}\z/us', $nom) !== 1) {
                $erreurs['nom'] = 'Le nom doit contenir entre 1 et 100 caractères.';
            }

            $donnees['nom'] = $nom;
        }

        if ($erreurs !== []) {
            throw new ValidationException($erreurs, 'Données invalides.');
        }

        $donnees['email'] = $email;

        return $donnees;
    }
}