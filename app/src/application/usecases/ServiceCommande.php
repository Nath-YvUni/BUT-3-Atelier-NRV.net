<?php
declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\dto\CommandeDetailDTO;
use nrv\application\dto\PaiementDTO;
use nrv\application\exceptions\CommandeEnAttenteException;
use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\PlacesInsuffisantesException;
use nrv\application\exceptions\ValidationException;
use nrv\application\ports\api\ServiceCommandeInterface;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\application\ports\spi\PanierRepositoryInterface;
use nrv\application\ports\api\ServiceBilletInterface;
use nrv\domain\entities\Commande;
use nrv\domain\entities\Panier;
use Override;

final class ServiceCommande implements ServiceCommandeInterface
{
    public function __construct(
        private CommandeRepositoryInterface $commandeRepository,
        private PanierRepositoryInterface $panierRepository,
        private ServiceBilletInterface $serviceBillet
    ) {
    }

    public function getUserCommandes(int $utilisateurId): array
    {
        return $this->commandeRepository->findbyUser($utilisateurId);
    }

    #[Override]
    public function getCommandeById(int $utilisateurId, int $commandeId): array
    {
        $commande = $this->commande($utilisateurId, $commandeId);

        if ($commande->getPanierId() !== null) {
            $panier = $this->panierRepository->trouverParUtilisateur($utilisateurId);
            $contenu = ['lignes' => $panier->getLignes(), 'pass' => $panier->getPass()];
        } else {
            $contenu = $this->commandeRepository->trouverContenuPaye($commandeId);
        }

        return CommandeDetailDTO::fromEntities($commande, $contenu['lignes'], $contenu['pass'])->toArray();
    }

    #[Override]
    public function creerCommande(int $utilisateurId, array $donnees): array
    {
        if (!$this->commandeRepository->commander($utilisateurId, $donnees)) {
            throw new CommandeEnAttenteException();
        }

        $panier = $this->panierRepository->trouverParUtilisateur($utilisateurId);

        $commande = $this->commandeRepository->findById(
            $utilisateurId,
            $panier->getCommandeEnAttenteId()
        );

        return CommandeDetailDTO::fromEntities($commande, $panier->getLignes(), $panier->getPass())->toArray();
    }


    #[Override]
    public function payerCommande(int $utilisateurId, int $commandeId, array $donnees): array
    {
        $commande = $this->commande($utilisateurId, $commandeId);

        $this->validerCarte($donnees);

        if ($commande->getStatut() === 'en_attente') {
            $panier = $this->panierRepository->trouverParUtilisateur($utilisateurId);

            $manques = $this->commandeRepository->payer(
                $commandeId,
                $panier,
                fn(int $id, Panier $panier) =>
                    $this->serviceBillet->genererBillets($id, $panier)
            );

            if ($manques !== []) {
                throw new PlacesInsuffisantesException($manques);
            }

            $commande = $this->commande($utilisateurId, $commandeId);
        }

        return PaiementDTO::fromEntity(
            $commande,
            $this->commandeRepository->compterBillets($commandeId)
        )->toArray();
    }


    private function commande(int $utilisateurId, int $commandeId): Commande
    {
        $commande = $this->commandeRepository->findById($utilisateurId, $commandeId);

        if ($commande === null) {
            throw new CommandeIntrouvableException();
        }

        return $commande;
    }

    private function validerCarte(array $donnees): void
    {
        $formats = [
            'numero_carte' => ['/^[0-9]{16}$/D', 'Le numéro de carte doit contenir 16 chiffres.'],
            'expiration' => ['/^[0-9]{4}-(0[1-9]|1[0-2])$/D', 'La date d’expiration doit être au format AAAA-MM.'],
            'cryptogramme' => ['/^[0-9]{3}$/D', 'Le cryptogramme doit contenir 3 chiffres.'],
        ];

        $erreurs = [];

        foreach ($formats as $champ => [$format, $message]) {
            if (!is_string($donnees[$champ] ?? null) || preg_match($format, $donnees[$champ]) !== 1) {
                $erreurs[$champ] = $message;
            }
        }

        if (!isset($erreurs['expiration']) && $donnees['expiration'] < (new \DateTimeImmutable())->format('Y-m')) {
            $erreurs['expiration'] = 'La carte est expirée.';
        }

        foreach (array_diff(array_keys($donnees), array_keys($formats)) as $champ) {
            $erreurs[$champ] = 'Champ non autorisé.';
        }

        if ($erreurs !== []) {
            throw new ValidationException($erreurs, 'Données invalides.');
        }
    }
}
