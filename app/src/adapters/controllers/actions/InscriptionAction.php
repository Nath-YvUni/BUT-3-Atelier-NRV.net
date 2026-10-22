<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

final class InscriptionAction extends AbstractAuthAction
{
    protected function traiter(array $donnees): array
    {
        return [
            'data' => $this->serviceAuth->inscrire($donnees),
            '_links' => [
                'connexion' => [
                    'href' => '/api/auth/connexion',
                    'method' => 'POST',
                ],
            ],
        ];
    }

    protected function statut(): int
    {
        return 201;
    }
}