<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\middlewares;

use Slim\Exception\HttpException;
use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

final class ApiErrorRenderer implements ErrorRendererInterface
{
    public function __invoke(
        Throwable $exception,
        bool $displayErrorDetails
    ): string {
        $statut = $exception instanceof HttpException
            ? $exception->getCode()
            : 500;

        $code = match ($statut) {
            400 => 'BAD_REQUEST',
            401 => 'AUTHENTIFICATION_REQUISE',
            403 => 'ACCES_INTERDIT',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            409 => 'CONFLICT',
            415 => 'FORMAT_NON_ACCEPTE',
            422 => 'VALIDATION_ERROR',
            default => $statut >= 500
                ? 'INTERNAL_SERVER_ERROR'
                : 'HTTP_ERROR',
        };

        $message = $statut >= 500
            ? 'Une erreur interne est survenue.'
            : ($exception->getMessage()
                ?: 'La requête ne peut pas être traitée.');

        return json_encode(
            [
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'details' => [],
                ],
            ],
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_INVALID_UTF8_SUBSTITUTE
            | JSON_THROW_ON_ERROR
        );
    }
}