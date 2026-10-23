<?php
declare(strict_types=1);


namespace nrv\application\ports\spi;

use nrv\domain\entities\Commande;

interface CommandeRepositoryInterface 
{
    public function findbyUser(int $utilisateurId) : array;

    public function findById(int $utilisateurId, int $commandeId) : ?Commande;

    public function commander(int $utilisateurId, array $donnees) : bool;

}