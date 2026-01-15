<?php

use App\Controllers\UsuarioController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;
use App\Constants\Roles;

/** @var \Slim\Slim $app */
$app = \Slim\Slim::getInstance();
$container = $app->di;

$app->group('/usuarios', function () use ($app, $container) {

    // =========================================================================
    // 1. RUTAS PÚBLICAS
    // =========================================================================
    
    // Login único. El registro público se eliminó por política de seguridad.
    $app->post('/login', function () use ($app, $container) {
        $datos = json_decode($app->request->getBody(), true);
        $controller = $container->get(UsuarioController::class);
        $controller->login($datos);
    });

    // =========================================================================
    // 2. RUTAS DE ADMINISTRACIÓN (Protegidas)
    // =========================================================================
    // El orden de ejecución de Middleware en Slim 2 es de abajo hacia arriba (cebolla),
    // pero aquí lo definimos lógicamente al final del grupo.
    
    $app->group('/admin', function () use ($app, $container) {

        // A. Listar Usuarios
        // El repositorio hidratará los objetos Usuario con sus objetos Rol anidados.
        $app->get('/listar', function () use ($app, $container) {
            $rolId = $app->request->get('rol_id');
            $controller = $container->get(UsuarioController::class);
            $controller->listarTodo($rolId);
        });

        // B. Crear Usuario (Full control para el Admin)
        $app->post('/crear', function () use ($app, $container) {
            $datos = json_decode($app->request->getBody(), true);
            $controller = $container->get(UsuarioController::class);
            $controller->crearAdmin($datos);
        });

        // C. Editar Usuario
        $app->put('/editar/:id', function ($id) use ($app, $container){
            $datos = json_decode($app->request->getBody(), true);
            $controller = $container->get(UsuarioController::class);
            $controller->editarAdmin($id, $datos);
        });

        // D. Eliminar Usuario (Soft Delete)
        $app->delete('/:id', function ($id) use ($app, $container) {
            // Obtenemos el usuario del contexto (inyectado por AuthMiddleware)
            // para evitar que se auto-elimine.
            $usuarioLogueado = $app->usuario; 
            
            $controller = $container->get(UsuarioController::class);
            $controller->eliminarAdmin($id, $usuarioLogueado);
        });

    })
    ->middleware(RolMiddleware::verificar([Roles::ADMIN])) // 2. Si pasa Auth, verifica si es Admin
    ->middleware(AuthMiddleware::verificar($app));         // 1. Verifica Token y firma

});