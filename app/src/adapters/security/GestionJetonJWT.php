<?php
declare(strict_types=1);

namespace nrv\adapters\security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use nrv\application\exceptions\AuthentificationException;
use nrv\application\ports\spi\GestionJetonInterface;

final readonly class GestionJetonJWT implements GestionJetonInterface
{
    private const EMETTEUR = 'nrv.net';
    private const DESTINATAIRE = 'nrv.web';

    public function __construct(
        private string $cleSecrete,
        private int $dureeVie = 3600,
    ) {
        if (strlen($cleSecrete) < 32) {
            throw new \InvalidArgumentException(
                'La clé JWT doit contenir au moins 32 octets.'
            );
        }

        if ($dureeVie <= 0) {
            throw new \InvalidArgumentException(
                'La durée de validité doit être positive.'
            );
        }
    }

    public function creer(int $utilisateurId): array
    {
        if ($utilisateurId <= 0) {
            throw new \InvalidArgumentException(
                'Identifiant utilisateur invalide.'
            );
        }

        $maintenant = time();

        $contenu = [
            'iss' => self::EMETTEUR,
            'aud' => self::DESTINATAIRE,
            'sub' => (string) $utilisateurId,
            'iat' => $maintenant,
            'nbf' => $maintenant,
            'exp' => $maintenant + $this->dureeVie,
        ];

        return [
            'access_token' => JWT::encode(
                $contenu,
                $this->cleSecrete,
                'HS256'
            ),
            'token_type' => 'Bearer',
            'expires_in' => $this->dureeVie,
        ];
    }

    public function lire(string $jeton): int
    {
        try {
            $contenu = JWT::decode(
                $jeton,
                new Key($this->cleSecrete, 'HS256')
            );
        } catch (\UnexpectedValueException | \DomainException $e) {
            throw new AuthentificationException(
                'Jeton invalide ou expiré.',
                0,
                $e
            );
        }

        if (
            ($contenu->iss ?? null) !== self::EMETTEUR
            || ($contenu->aud ?? null) !== self::DESTINATAIRE
            || !is_int($contenu->exp ?? null)
            || !is_string($contenu->sub ?? null)
        ) {
            throw new AuthentificationException(
                'Jeton invalide ou expiré.'
            );
        }

        $identifiant = filter_var(
            $contenu->sub,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($identifiant === false) {
            throw new AuthentificationException(
                'Jeton invalide ou expiré.'
            );
        }

        return $identifiant;
    }
}