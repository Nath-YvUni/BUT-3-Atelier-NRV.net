<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

final class AjouterLignePanierAction extends AbstractPanierAction
{
    protected function traiter(int $utilisateurId, array $donnees): array
    {
        $resultat = $this->servicePanier->ajouterBillets($utilisateurId, $donnees);

        return [$resultat['panier'], $resultat['cree'] ? 201 : 200];
    }
}
