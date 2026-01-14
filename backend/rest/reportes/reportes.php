<?php

use App\Controllers\ReporteController;

$app = \Slim\Slim::getInstance();
$container = $app->di;

$app->group('/reportes', function () use ($app, $container) {

    $app->get('/dashboard', function () use ($container) {
        $controller = $container->get(ReporteController::class);
        $controller->dashboardGeneral();
    });

});