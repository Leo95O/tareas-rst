<?php

use App\Controllers\UsuarioController;

$app = \Slim\Slim::getInstance();
$container = $app->di;

// Rutas Públicas
$app->post('/registro', function () use ($container) {
    $controller = $container->get(UsuarioController::class);
    $controller->registrar();
});

$app->post('/login', function () use ($container) {
    $controller = $container->get(UsuarioController::class);
    $controller->login();
});

// Rutas Privadas (Admin)
$app->group('/usuarios', function () use ($app, $container) {

    // Listar usuarios (Admin/PM)
    $app->get('/', function () use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->listarTodo();
    });

    // Crear usuario desde panel Admin
    $app->post('/', function () use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->crearAdmin();
    });

    // Editar usuario desde panel Admin
    $app->put('/:id', function ($id) use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->editarAdmin($id);
    });

    // Eliminar usuario
    $app->delete('/:id', function ($id) use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->eliminarAdmin($id);
    });

});