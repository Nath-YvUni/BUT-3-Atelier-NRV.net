<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\actions;

use nrv\application\exceptions\AuthentificationException;
use nrv\application\exceptions\EmailDejaUtiliseException;
use nrv\application\exceptions\ValidationException;
use nrv\application\ports\api\ServiceAuthInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractAuthAction extends AbstractAction
{
    public function __construct(
        protected ServiceAuthInterface $serviceAuth
    ) {
    }

    abstract protected function traiter(array $donnees): array;

    protected function statut(): int
    {
        return 200;
    }

    final public function __invoke(
        ServerRequestInterface $rq,
        ResponseInterface $rs,
        array $args
    ): ResponseInterface {
        $type = strtolower(trim(
            explode(';', $rq->getHeaderLine('Content-Type'), 2)[0]
        ));

        if ($type !== 'application/json') {
            return $this->erreur(
                $rs,
                415,
                'FORMAT_NON_ACCEPTE',
                'Le corps doit être envoyé en application/json.'
            );
        }

        try {
            $donnees = json_decode(
                (string) $rq->getBody(),
                false,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            return $this->erreur(
                $rs,
                400,
                'JSON_INVALIDE',
                'Le corps JSON est mal formé.'
            );
        }

        if (!$donnees instanceof \stdClass) {
            return $this->erreur(
                $rs,
                400,
                'JSON_INVALIDE',
                'Le corps JSON doit être un objet.'
            );
        }

        try {
            $resultat = $this->traiter((array) $donnees);
        } catch (ValidationException $e) {
            $details = [];

            foreach ($e->getErrors() as $champ => $message) {
                $details[] = [
                    'champ' => $champ,
                    'message' => $message,
                ];
            }

            return $this->erreur(
                $rs,
                422,
                'VALIDATION_ERROR',
                $e->getMessage(),
                $details
            );
        } catch (EmailDejaUtiliseException $e) {
            return $this->erreur(
                $rs,
                409,
                'EMAIL_DEJA_UTILISE',
                $e->getMessage()
            );
        } catch (AuthentificationException $e) {
            return $this->erreur(
                $rs,
                401,
                'AUTHENTIFICATION_ECHOUEE',
                $e->getMessage()
            );
        }

        return $this->json($rs, $resultat, $this->statut());
    }

    private function erreur(
        ResponseInterface $rs,
        int $statut,
        string $code,
        string $message,
        array $details = []
    ): ResponseInterface {
        return $this->json($rs, [
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $statut);
    }
}