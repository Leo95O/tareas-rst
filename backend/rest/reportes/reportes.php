<?php

use App\Controllers\ReporteController;
use App\Middleware\AuthMiddleware;
use App\Middleware\ActiveUserMiddleware;
use App\Middleware\RolMiddleware;
use App\Constants\Roles;
use App\Utils\ApiResponse;

/** @var \Slim\Slim $app */
$container = $app->di;

// =============================================================================
// HELPER: Decodificador JSON Seguro (Para estandarización futura)
// =============================================================================
$getJson = function () use ($app) {
    $body = $app->request->getBody();
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $app->response->status(400);
        echo ApiResponse::alerta(["JSON inválido o mal formado."], []); 
        $app->stop();
    }
    return $data;
};

// =============================================================================
// MIDDLEWARES (Instancia Única)
// =============================================================================
$auth   = AuthMiddleware::verificar($app);
$active = ActiveUserMiddleware::verificar($app);

// Permisos para estadísticas avanzadas (Solo Jefes)
$rolesAdminPM = RolMiddleware::verificar($app, [Roles::ADMIN, Roles::PROJECT_MANAGER]);

// =============================================================================
// RUTAS DE REPORTES
// =============================================================================

$app->group('/reportes', function () use ($app, $container, $getJson, $auth, $active, $rolesAdminPM) {

    /**
     * 1. Dashboard Principal (Personalizado o Global)
     * GET /reportes/dashboard
     * Auth + Active
     */
    $app->get('/dashboard', $auth, $active, function () use ($app, $container) {
        // Obtenemos el usuario del contexto (inyectado por ActiveUserMiddleware)
        $usuario = $app->usuario;
        
        // LÓGICA DE VISUALIZACIÓN INTELIGENTE:
        // - Si es rol USER (3), le pasamos su ID para que filtre solo sus tareas.
        // - Si es ADMIN (1) o PM (2), pasamos NULL para que traiga totales globales.
        $filtroId = ($usuario->rol_id == Roles::USER) ? $usuario->usuario_id : null;

        $container->get(ReporteController::class)->dashboard($filtroId);
    });

    /**
     * 2. Estadísticas Administrativas Avanzadas
     * GET /reportes/admin-stats
     * Auth + Active + ROL (Solo Admin/PM)
     */
    $app->get('/admin-stats', $auth, $active, $rolesAdminPM, function () use ($container) {
        $container->get(ReporteController::class)->adminStats();
    });

});