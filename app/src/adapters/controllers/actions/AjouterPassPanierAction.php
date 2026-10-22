<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

final class AjouterPassPanierAction extends AbstractPanierAction
{
    protected function traiter(int $utilisateurId, array $donnees): array
    {
        return [$this->servicePanier->ajouterPass($utilisateurId, $donnees), 201];
    }
}
