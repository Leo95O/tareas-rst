<?php

use App\Controllers\UsuarioController;

$app = \Slim\Slim::getInstance();
$container = $app->di;

// CAMBIO: Iniciamos el grupo '/usuarios' desde el principio
$app->group('/usuarios', function () use ($app, $container) {

    // Ahora la ruta será: /usuarios/registro
    $app->post('/registro', function () use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->registrar();
    });

    // Ahora la ruta será: /usuarios/login (Coincide con tu Frontend)
    $app->post('/login', function () use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->login();
    });

    // Rutas Privadas (Admin/PM)
    // Ya estamos dentro de /usuarios, así que estas rutas son /usuarios/ (listar), /usuarios/:id, etc.
    
    // Listar usuarios
    $app->get('/', function () use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->listarTodo();
    });

    // Crear usuario admin
    $app->post('/', function () use ($container) {
        $controller = $container->get(UsuarioController::class);
        $controller->crearAdmin();
    });

    // Editar usuario
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