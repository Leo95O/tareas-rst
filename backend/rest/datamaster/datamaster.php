<?php

use App\Controllers\DataMasterController;

$app = \Slim\Slim::getInstance();
$container = $app->di;

$app->group('/datamaster', function () use ($app, $container) {

    $app->get('/categorias', function () use ($container) {
        $controller = $container->get(DataMasterController::class);
        $controller->getCategorias();
    });

    $app->get('/prioridades', function () use ($container) {
        $controller = $container->get(DataMasterController::class);
        $controller->getPrioridades();
    });

    $app->get('/estados', function () use ($container) {
        $controller = $container->get(DataMasterController::class);
        $controller->getEstados();
    });

    $app->get('/sucursales', function () use ($container) {
        $controller = $container->get(DataMasterController::class);
        $controller->getSucursales();
    });

    $app->get('/estados-proyecto', function () use ($container) {
        $controller = $container->get(DataMasterController::class);
        $controller->getEstadosProyecto();
    });

});