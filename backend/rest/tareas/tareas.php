<?php

use App\Controllers\TareaController;

$app = \Slim\Slim::getInstance();
$container = $app->di;

$app->group('/tareas', function () use ($app, $container) {

    // 1. Rutas Específicas (Deben ir antes de /:id para evitar conflictos)
    
    // Bolsa de tareas (sin asignar)
    $app->get('/bolsa', function () use ($container) {
        $controller = $container->get(TareaController::class);
        $controller->listarBolsa();
    });

    // 2. Rutas Generales
    
    // Listar tareas (filtra por rol internamente)
    $app->get('/', function () use ($container) {
        $controller = $container->get(TareaController::class);
        $controller->listar();
    });

    // Crear tarea
    $app->post('/', function () use ($container) {
        $controller = $container->get(TareaController::class);
        $controller->crear();
    });

    // 3. Rutas con ID
    
    // Editar tarea
    $app->put('/:id', function ($id) use ($container) {
        $controller = $container->get(TareaController::class);
        $controller->editar($id);
    });

    // Eliminar tarea
    $app->delete('/:id', function ($id) use ($container) {
        $controller = $container->get(TareaController::class);
        $controller->eliminar($id);
    });

    // Auto-asignarse una tarea
    $app->put('/:id/asignarme', function ($id) use ($container) {
        $controller = $container->get(TareaController::class);
        $controller->asignarme($id);
    });

});