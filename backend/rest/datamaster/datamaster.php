<?php

use App\Controllers\DataMasterController;
use App\Middleware\AuthMiddleware;
use App\Middleware\ActiveUserMiddleware;

/** @var \Slim\Slim $app */
$app = \Slim\Slim::getInstance();
$container = $app->di;

// GRUPO DATAMASTER
// Protegido: Solo usuarios con Token válido y Cuenta Activa
$app->group('/datamaster', 
    AuthMiddleware::verificar($app), 
    ActiveUserMiddleware::verificar($app),
    function () use ($app, $container) {

    // Ruta única para hidratar todos los selectores del frontend
    $app->get('/catalogos', function () use ($app, $container) {
        /** @var DataMasterController $controller */
        $controller = $container->get(DataMasterController::class);
        $controller->obtenerCatalogos();
    });

});