<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\ports\api\ServiceBilletInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetBilletsByUserAction
{
    public function __construct(
        private ServiceBilletInterface $serviceBillet
    ) {
    }

    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {
        $utilisateur = $rq->getAttribute('utilisateur');

        if (!is_array($utilisateur)) {
            $rs = $rs->withStatus(401);
            $rs->getBody()->write(json_encode([
                'error' => 'Authentification requise.'
            ]));

            return $rs->withHeader('Content-Type', 'application/json');
        }



        error_log('UTILISATEUR : ' . json_encode($utilisateur));

        $billets = $this->serviceBillet->getUserBillets(
            (int) $utilisateur['id']
        );

        error_log('NOMBRE BILLETS : ' . count($billets));

        $rs->getBody()->write(json_encode([
            'data' => $billets
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $rs->withHeader('Content-Type', 'application/json');
    }
}
