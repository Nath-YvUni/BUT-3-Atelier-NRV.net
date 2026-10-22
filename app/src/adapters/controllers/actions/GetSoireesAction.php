<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\exceptions\ValidationException;
use nrv\application\ports\api\ServiceSoireeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetSoireesAction extends AbstractAction
{
    public function __construct(
        private ServiceSoireeInterface $serviceSoiree
    ) {
    }

    public function __invoke(ServerRequestInterface $rq, ResponseInterface $rs,array $args): ResponseInterface {
        $date = $rq->getQueryParams()['date'] ?? null;

        try {
            if ($date !== null && !is_string($date)) {
                throw new ValidationException(
                    ['date' => 'Une seule date doit être transmise.'],
                    'Date invalide.'
                );
            }

            $soirees = $this->serviceSoiree->getSoirees($date);
        } catch (ValidationException $e) {
            $details = [];

            foreach ($e->getErrors() as $champ => $message) {
                $details[] = [
                    'champ' => $champ,
                    'message' => $message,
                ];
            }

            return $this->json($rs, [
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => $e->getMessage(),
                    'details' => $details,
                ],
            ], 422);
        }

        $href = '/api/soirees';

        if ($date !== null && $date !== '') {
            $href .= '?' . http_build_query(['date' => $date], '', '&', PHP_QUERY_RFC3986);
        }

        return $this->json($rs, [
            'data' => $soirees,
            '_links' => [
                'self' => [
                    'href' => $href,
                    'method' => 'GET',
                ],
            ],
        ]);
    }
}
