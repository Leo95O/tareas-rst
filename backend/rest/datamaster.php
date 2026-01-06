<?php

use App\Controllers\DataMasterController;

$app = \Slim\Slim::getInstance();

$app->group('/datamaster', function () use ($app) {

    $app->get('/categorias', function () {
        (new DataMasterController())->getCategorias();
    });

    $app->get('/prioridades', function () {
        (new DataMasterController())->getPrioridades();
    });

    $app->get('/estados', function () {
        (new DataMasterController())->getEstados();
    });

    $app->get('/sucursales', function () {
        (new DataMasterController())->getSucursales();
    });

    $app->get('/estados-proyecto', function () {
        (new DataMasterController())->getEstadosProyecto();
    });

});