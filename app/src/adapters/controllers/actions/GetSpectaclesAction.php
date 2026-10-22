<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\ports\api\ServiceSpectacleInterface;
use nrv\application\exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetSpectaclesAction extends AbstractAction
{

    private ServiceSpectacleInterface $serviceSpectacle;

    function __construct(ServiceSpectacleInterface $serviceSpectacle)
    {
        $this->serviceSpectacle = $serviceSpectacle;
    }

    public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {
        $parametres = $rq->getQueryParams();
        $date = $parametres['date'] ?? null;
        $styleId = $parametres['style_id'] ?? null;

        try {
            if (array_key_exists('style', $parametres)) {
                throw new ValidationException(
                    ['style' => 'Utilisez le paramètre style_id.'],
                    'Paramètre de filtre invalide.'
                );
            }
            if ($date !== null && !is_string($date)) {
                throw new ValidationException(
                    ['date' => 'Une seule date doit être transmise.'],
                    'Date invalide.'
                );
            }

            if ($styleId !== null && !is_string($styleId)) {
                throw new ValidationException(
                    ['style_id' => 'Un seul identifiant de style doit être transmis.'],
                    'Style invalide.'
                );
            }

            $spectacles = $this->serviceSpectacle->getSpectacles($date, $styleId);

            return $this->json($rs, $spectacles);
        } catch (ValidationException $e) {
            $details = [];

            foreach ($e->getErrors() as $champ => $erreur) {
                $details[] = [
                    'champ' => $champ,
                    'message' => $erreur,
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
    }
}
