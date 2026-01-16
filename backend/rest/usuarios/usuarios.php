<?php

use App\Controllers\UsuarioController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;
use App\Middleware\ActiveUserMiddleware;
use App\Constants\Roles;

/** @var \Slim\Slim $app */
$app = \Slim\Slim::getInstance();
$container = $app->di;

// GRUPO PRINCIPAL: /usuarios
// Nota: Aquí NO aplicamos seguridad todavía, para permitir rutas públicas dentro.
$app->group('/usuarios', function () use ($app, $container) {

    // =============================================================
    // 1. ZONA PÚBLICA (Sin Token ni Verificación de Usuario)
    // =============================================================
    
    // POST /usuarios/login
    $app->post('/login', function () use ($app, $container) {
        $datos = json_decode($app->request->getBody(), true);
        /** @var UsuarioController $controller */
        $controller = $container->get(UsuarioController::class);
        $controller->login($datos);
    });

    // Si tuvieras registro público, iría aquí también:
    // $app->post('/registro', ...);


    // =============================================================
    // 2. ZONA PROTEGIDA (Requiere Token + Usuario Activo)
    // =============================================================
    
    // Creamos un sub-grupo para aplicar los middlewares de seguridad en cadena solo a lo de adentro
    $app->group('/', 
        AuthMiddleware::verificar($app),       // 1. Validar Token JWT
        ActiveUserMiddleware::verificar($app), // 2. Validar que no esté baneado
        function () use ($app, $container) {

        // --- RUTAS ADMIN ---
        // Estas rutas ya heredan la seguridad del grupo padre, y añaden la del Rol
        $app->group('/admin', RolMiddleware::verificar($app, [Roles::ADMIN]), function () use ($app, $container) {
            
            /** @var \Slim\Slim $app */

            $app->get('/listar', function () use ($app, $container) {
                $rolId = $app->request->get('rol_id');
                $controller = $container->get(UsuarioController::class);
                $controller->listarTodo($rolId);
            });

            $app->post('/crear', function () use ($app, $container) {
                $datos = json_decode($app->request->getBody(), true);
                $controller = $container->get(UsuarioController::class);
                $controller->crearAdmin($datos);
            });

            $app->put('/editar/:id', function ($id) use ($app, $container){
                $datos = json_decode($app->request->getBody(), true);
                $controller = $container->get(UsuarioController::class);
                $controller->editarAdmin($id, $datos);
            });

            $app->delete('/:id', function ($id) use ($app, $container) {
                /** @var \Slim\Slim $app */
                // El usuario ya fue inyectado por AuthMiddleware
                $usuarioLogueado = $app->usuario; 
                
                $controller = $container->get(UsuarioController::class);
                $controller->eliminarAdmin($id, $usuarioLogueado);
            });
        });

        // Aquí podrías agregar otras rutas protegidas para usuarios normales
        // Ej: $app->get('/perfil', ...);

    }); // Fin del grupo protegido

}); // Fin del grupo /usuarios