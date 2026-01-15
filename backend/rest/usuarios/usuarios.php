<?php

use App\Controllers\UsuarioController;

$app = \Slim\Slim::getInstance();
$container = $app->di;


$app->group('/usuarios', function () use ($app, $container) {

    $app->post('/registro', function () use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->registrar();
    });


    $app->post('/login', function () use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->login();
    });

    // --- Rutas Privadas (Admin/PM) ---
    
    // GET /usuarios/
    $app->get('/', function () use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->listarTodo();
    });

    // POST /usuarios/ (Crear Admin)
    $app->post('/', function () use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->crearAdmin();
    });

    // PUT /usuarios/:id
    $app->put('/:id', function ($id) use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->editarAdmin($id);
    });

    // DELETE /usuarios/:id
    $app->delete('/:id', function ($id) use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->eliminarAdmin($id);
    });

});