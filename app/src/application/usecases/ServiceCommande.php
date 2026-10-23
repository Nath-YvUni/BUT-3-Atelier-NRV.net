<?php
declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\dto\CommandeDetailDTO;
use nrv\application\exceptions\CommandeEnAttenteException;
use nrv\application\ports\api\ServiceCommandeInterface;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\application\ports\spi\PanierRepositoryInterface;
use Override;

final class ServiceCommande implements ServiceCommandeInterface
{
    public function __construct(
        private CommandeRepositoryInterface $commandeRepository,
        private PanierRepositoryInterface $panierRepository,
    ) {
    }

    public function getUserCommandes(int $utilisateurId) : array{
        return $this->commandeRepository->findbyUser($utilisateurId);
    }

    #[Override]
    public function getCommandeById(int $utilisateurId, int $commandeId)
    {
        return $this->commandeRepository->findById($utilisateurId, $commandeId);
    }

    #[Override]
    public function creerCommande(int $utilisateurId, array $donnees) : array
    {
        if (!$this->commandeRepository->commander($utilisateurId, $donnees)) {
            throw new CommandeEnAttenteException();
        }

        $panier = $this->panierRepository->trouverParUtilisateur($utilisateurId);

        $commande = $this->commandeRepository->findById(
            $utilisateurId,
            $panier->getCommandeEnAttenteId()
        );

        return CommandeDetailDTO::fromEntities($commande, $panier)->toArray();
    }

}
