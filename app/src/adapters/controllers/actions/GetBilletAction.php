<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\exceptions\BilletIntrouvableException;
use nrv\application\ports\api\ServiceBilletInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

final class GetBilletAction extends AbstractAction
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
        $token = $args['token'] ?? '';

        try {
            $billet = $this->serviceBillet->consulterBillet(
                is_string($token) ? $token : ''
            );
        } catch (BilletIntrouvableException $e) {
            throw new HttpNotFoundException($rq, 'Ce billet n’existe pas.');
        }

        return $this->json($rs, [
            'data' => $billet,
            '_links' => [
                'self' => [
                    'href' => '/api/billets/' . $billet['token'],
                    'method' => 'GET',
                ],
            ],
        ])->withHeader('Cache-Control', 'private, no-store');
    }
}
