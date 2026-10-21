<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

final class ConnexionAction extends AbstractAuthAction
{
    protected function traiter(array $donnees): array
    {
        return [
            'data' => $this->serviceAuth->connecter($donnees),
            '_links' => [
                'profil' => [
                    'href' => '/api/me',
                    'method' => 'GET',
                ],
            ],
        ];
    }
}