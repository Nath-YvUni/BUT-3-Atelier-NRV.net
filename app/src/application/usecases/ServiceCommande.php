<?php
declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\ports\api\ServiceCommandeInterface;
use Override;

final class ServiceCommande implements ServiceCommandeInterface
{
    public function __construct(private CommandeRepositoryInterface $commandeRepository,) {
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
    public function creerCommande(int $utilisateurId, array $donnees)
    {
        $this->commandeRepository->commander($utilisateurId, $donnees);
    }

}
