<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

use nrv\application\ports\api\ServiceSoireeInterface;
use nrv\application\exceptions\SoireeIntrouvableException;


class GetDetailsSoireeAction extends AbstractAction
{

    private ServiceSoireeInterface $serviceSoiree;

    public function __construct(ServiceSoireeInterface $serviceSoiree)
    {
        $this->serviceSoiree = $serviceSoiree;
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs, array $args): ResponseInterface
    {
        $id = $args['id'] ?? '';

        $identifiant = is_string($id) && ctype_digit($id)
            ? filter_var(
                $id,
                FILTER_VALIDATE_INT,
                [
                    'options' => [
                        'min_range' => 1,
                        'max_range' => 2147483647,
                    ],
                ]
            )
            : false;

        if ($identifiant === false) {
            throw new HttpNotFoundException(
                $rq,
                'Cette soirée n’existe pas.'
            );
        }

        try {
            $soireeDTO = $this->serviceSoiree->getDetails($identifiant);
        } catch (SoireeIntrouvableException $e) {
            throw new HttpNotFoundException(
                $rq,
                'Cette soirée n’existe pas.'
            );
        }

        return $this->json($rs, [
            'data' => $soireeDTO->toArray(),
            '_links' => [
                'self' => [
                    'href' => '/api/soirees/' . $identifiant,
                    'method' => 'GET',
                ],
            ],
        ]);
    }
}