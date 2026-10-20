<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;

use nrv\adapters\config\ContainerConfig;
use nrv\adapters\controllers\actions\GetDetailsSoireeAction;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, '.env');
$dotenv->load();
$container = ContainerConfig::build();

$app = AppFactory::createFromContainer($container);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, false, false);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');
$errorHandler->registerErrorRenderer('application/json', function ($exception, $displayErrorDetails) {
    return json_encode(['message' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
});

// routes

$app->group('/soirees', function (\Slim\Routing\RouteCollectorProxy $group) {
    $group->get('/{id}', GetDetailsSoireeAction::class)
        ->setName('soiree-detail');
});

return $app;
