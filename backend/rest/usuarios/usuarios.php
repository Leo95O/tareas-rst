
<?php

use App\Controllers\UsuarioController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;
use App\Middleware\ActiveUserMiddleware;
use App\Constants\Roles;
use App\Utils\ApiResponse;
/** * @var \Slim\Slim $app 
 * @var \DI\Container $container
 */
$container = $app->di;

// =============================================================================
// HELPER LOCAL: Parseo de JSON seguro (DRY Pattern)
// =============================================================================
$getJson = function () use ($app) {
    $body = $app->request->getBody();
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $app->response->status(400);
        // AHORA SÍ: Usamos tu estándar ApiResponse
        echo ApiResponse::alerta( ["JSON inválido o mal formado."], []); 
        $app->stop();
    }
    return $data;
};

// =============================================================================
// PREPARACIÓN DE MIDDLEWARES (Instanciación Única)
// =============================================================================
// Creamos las instancias de seguridad una sola vez para optimizar memoria.
// Luego las pasamos como variables a cada ruta que las necesite.

$auth       = AuthMiddleware::verificar($app);
$active     = ActiveUserMiddleware::verificar($app);
$rolAdmin   = RolMiddleware::verificar($app, [Roles::ADMIN]);

// =============================================================================
// RUTAS DE USUARIOS
// =============================================================================

$app->group('/usuarios', function () use ($app, $container, $getJson, $auth, $active, $rolAdmin) {

    // -------------------------------------------------------------------------
    // 1. ZONA PÚBLICA (Sin Middlewares de seguridad)
    // -------------------------------------------------------------------------
    
    $app->post('/login', function () use ($container, $getJson) {
        $container->get(UsuarioController::class)->login($getJson());
    });

    // -------------------------------------------------------------------------
    // 2. ZONA ADMIN
    // -------------------------------------------------------------------------
    // Usamos group() solo para el prefijo de URL '/admin'.
    // La seguridad ($auth, $active, $rolAdmin) se inyecta en cada verbo HTTP.
    
    $app->group('/admin', function () use ($app, $container, $getJson, $auth, $active, $rolAdmin) {
            
        // LISTAR: Requiere Auth + Active + Admin
        $app->get('/listar', $auth, $active, $rolAdmin, function () use ($app, $container) {
            $rolId = $app->request->get('rol_id');
            $container->get(UsuarioController::class)->listarTodo($rolId);
        });

        // CREAR: Requiere Auth + Active + Admin
        $app->post('/crear', $auth, $active, $rolAdmin, function () use ($container, $getJson) {
            $container->get(UsuarioController::class)->crearAdmin($getJson());
        });

        // EDITAR: Requiere Auth + Active + Admin
        $app->put('/editar/:id', $auth, $active, $rolAdmin, function ($id) use ($container, $getJson) {
            $container->get(UsuarioController::class)->editarAdmin($id, $getJson());
        });

        // ELIMINAR: Requiere Auth + Active + Admin
        $app->delete('/:id', $auth, $active, $rolAdmin, function ($id) use ($app, $container) {
            // $app->usuario es inyectado previamente por AuthMiddleware
            $usuarioLogueado = $app->usuario; 
            $container->get(UsuarioController::class)->eliminarAdmin($id, $usuarioLogueado);
        });
    });

});