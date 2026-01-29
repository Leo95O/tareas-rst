<?php

use App\Controllers\DataMasterController;
use App\Middleware\AuthMiddleware;
use App\Middleware\ActiveUserMiddleware;

/** @var \Slim\Slim $app */
$container = $app->di;

// =============================================================================
// MIDDLEWARES (Instancia Única)
// =============================================================================
$auth   = AuthMiddleware::verificar($app);
$active = ActiveUserMiddleware::verificar($app);

// =============================================================================
// RUTAS DATAMASTER
// =============================================================================

$app->group('/datamaster', function () use ($app, $container, $auth, $active) {

    /**
     * Ruta única para hidratar todos los selectores del frontend
     * GET /datamaster/catalogos
     * Auth + Active
     */
    $app->get('/catalogos', $auth, $active, function () use ($container) {
        $container->get(DataMasterController::class)->obtenerCatalogos();
    });

});