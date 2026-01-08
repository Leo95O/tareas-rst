<?php

use App\Controllers\UsuarioController;
use App\Middleware\RolMiddleware;

$app = \Slim\Slim::getInstance();

$app->group('/usuarios', function () use ($app) {

    // Rutas Públicas (Definidas en el Middleware)
    $app->post('/registro', function () {
        $controller = new UsuarioController();
        $controller->registrar();
    });

    $app->post('/login', function () {
        $controller = new UsuarioController();
        $controller->login();
    });

    // Rutas Privadas 
    $app->get('/perfil', function () use ($app) {
        $usuario = $app->usuario;
        \App\Utils\ApiResponse::exito("Perfil recuperado", [
            'nombre' => \App\Utils\Crypto::desencriptar($usuario->usuario_nombre),
            'email' => $usuario->usuario_correo
        ]);
    });

    // --- RUTAS DE ADMINISTRADOR (Gestión de Usuarios) ---
    // Protegidas con RolMiddleware para Rol 1 (Admin)
    $app->group('/admin', RolMiddleware::verificar([1]), function () use ($app) {

        $app->get('/listar', function () {
            (new UsuarioController())->listarTodo();
        });

        $app->post('/crear', function () {
            (new UsuarioController())->crearAdmin();
        });

        $app->put('/editar/:id', function ($id) {
            (new UsuarioController())->editarAdmin($id);
        });

        $app->delete('/eliminar/:id', function ($id) {
            (new UsuarioController())->eliminarAdmin($id);
        });

    });

});