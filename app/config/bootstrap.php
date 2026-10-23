<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;

use nrv\adapters\config\ContainerConfig;
use nrv\adapters\controllers\actions\GetSpectaclesAction;
use nrv\adapters\controllers\actions\GetDetailsSoireeAction;
use nrv\adapters\controllers\actions\GetSoireesAction;

use nrv\adapters\controllers\actions\InscriptionAction;
use nrv\adapters\controllers\actions\ConnexionAction;
use nrv\adapters\controllers\actions\ProfilAction;
use nrv\adapters\controllers\middlewares\ApiErrorRenderer;
use nrv\adapters\controllers\middlewares\AuthentificationMiddleware;
use nrv\adapters\controllers\actions\AjouterLignePanierAction;
use nrv\adapters\controllers\actions\AjouterPassPanierAction;
use nrv\adapters\controllers\actions\GetTarifsAction;
use nrv\adapters\controllers\actions\GetPanierAction;
use nrv\adapters\controllers\actions\AjouterCommandeAction;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, '.env');
$dotenv->load();
$container = ContainerConfig::build();

$app = AppFactory::createFromContainer($container);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});
$errorMiddleware = $app->addErrorMiddleware(false, true, true);

$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');
$errorHandler->registerErrorRenderer(
    'application/json',
    new ApiErrorRenderer()
);

$app->get("/api/spectacles", GetSpectaclesAction::class)->setName("spectacles");
// routes

$app->group('/api/soirees', function (\Slim\Routing\RouteCollectorProxy $group) {
    $group->get('', GetSoireesAction::class)
        ->setName('soirees');
    $group->get('/{id}', GetDetailsSoireeAction::class)
        ->setName('soiree-detail');
});

$app->get('/api/tarifs', GetTarifsAction::class)
    ->setName('tarifs');

$app->post('/api/utilisateurs', InscriptionAction::class)
    ->setName('auth-inscription');

$app->post('/api/auth/connexion', ConnexionAction::class)
    ->setName('auth-connexion');

$app->get('/api/me', ProfilAction::class)
    ->setName('auth-profil')
    ->add(AuthentificationMiddleware::class);

    $app->get('/api/panier', GetPanierAction::class)
    ->setName('panier-consulter')
    ->add(AuthentificationMiddleware::class);

$app->post('/api/panier/lignes', AjouterLignePanierAction::class)
    ->setName('panier-ajout-ligne')
    ->add(AuthentificationMiddleware::class);

$app->post('/api/panier/pass', AjouterPassPanierAction::class)
    ->setName('panier-ajout-pass')
    ->add(AuthentificationMiddleware::class);

$app->post('/api/commandes', AjouterCommandeAction::class)
    ->setName('ajouter-commande')
    ->add(AuthentificationMiddleware::class);



return $app;
