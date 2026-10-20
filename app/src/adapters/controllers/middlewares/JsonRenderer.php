<?php
declare(strict_types=1);

namespace nrv\adapters\controllers\middlewares;

use Psr\Http\Message\ResponseInterface;

class JsonRenderer
{
    public static function render(ResponseInterface $rs, int $status, mixed $data = null): ResponseInterface
    {
        if ($data !== null) {
            $rs->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $rs
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
