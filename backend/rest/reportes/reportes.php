<?php

use App\Controllers\ReporteController;
use App\Middleware\RolMiddleware;

$app = \Slim\Slim::getInstance();

// Protegido para Roles 1 y 2
$app->group('/reportes', RolMiddleware::verificar([1, 2]), function () use ($app) {

    // GET /reportes/dashboard
    $app->get('/dashboard', function () {
        (new ReporteController())->dashboardGeneral();
    });

});