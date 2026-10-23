<?php
declare(strict_types=1);

namespace nrv\application\ports\api;

interface ServiceCommandeInterface {
    
    public function getUserCommandes(int $utilisateurId) : array;

    public function creerCommande(int $utilisateurId, array $donnees);

    public function getCommandeById(int $utilisateurId, int $commandeId);
}