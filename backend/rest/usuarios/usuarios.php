<?php
// backend/rest/usuarios/usuarios.php

use App\Controllers\UsuarioController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;
use App\Constants\Roles;

$app = \Slim\Slim::getInstance();
$container = $app->di;

$app->group('/usuarios', function () use ($app, $container) {

    // --- RUTAS PÚBLICAS (Solo Login) ---

    $app->post('/login', function () use ($app, $container) {
        $datos = json_decode($app->request->getBody(), true);
        $controller = $container->get(UsuarioController::class);
        $controller->login($datos);
    });

    // --- RUTAS DE ADMINISTRADOR (Protegidas) ---
    // Requieren Token (Auth) + Rol de Administrador (RolMiddleware)
    
    $app->group('/admin', function () use ($app, $container) {

        // 1. Listar Usuarios
        $app->get('/listar', function () use ($app, $container) {
            $rolId = $app->request->get('rol_id');
            $controller = $container->get(UsuarioController::class);
            $controller->listarTodo($rolId);
        });

        // 2. Crear Usuario (Solo Admin)
        $app->post('/crear', function () use ($app, $container) {
            $datos = json_decode($app->request->getBody(), true);
            $controller = $container->get(UsuarioController::class);
            $controller->crearAdmin($datos);
        });

        // 3. Editar Usuario
        $app->put('/editar/:id', function ($id) use ($app, $container){
            $datos = json_decode($app->request->getBody(), true);
            $controller = $container->get(UsuarioController::class);
            $controller->editarAdmin($id, $datos);
        });

        // 4. Eliminar Usuario
        $app->delete('/:id', function ($id) use ($app, $container) {
            // Necesitamos el usuario logueado para no auto-eliminarse
            $usuarioLogueado = $app->usuario; 
            $controller = $container->get(UsuarioController::class);
            $controller->eliminarAdmin($id, $usuarioLogueado);
        });

    })->middleware(RolMiddleware::verificar([Roles::ADMIN])) 
      ->middleware(AuthMiddleware::verificar($app)); // Pasamos $app por inyección

});