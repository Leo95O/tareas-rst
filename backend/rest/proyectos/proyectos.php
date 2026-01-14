<?php

use App\Controllers\ProyectoController;

$app = \Slim\Slim::getInstance();
$container = $app->di;

$app->group('/proyectos', function () use ($app, $container) {

    // Listar todos los proyectos
    $app->get('/', function () use ($container) {
        $controller = $container->get(ProyectoController::class);
        $controller->listar();
    });

    // Crear proyecto
    $app->post('/', function () use ($container) {
        $controller = $container->get(ProyectoController::class);
        $controller->crear();
    });

    // Obtener un proyecto específico
    $app->get('/:id', function ($id) use ($container) {
        $controller = $container->get(ProyectoController::class);
        $controller->obtenerPorId($id);
    });

    // Editar proyecto
    $app->put('/:id', function ($id) use ($container) {
        $controller = $container->get(ProyectoController::class);
        $controller->editar($id);
    });

    // Eliminar proyecto
    $app->delete('/:id', function ($id) use ($container) {
        $controller = $container->get(ProyectoController::class);
        $controller->eliminar($id);
    });

});