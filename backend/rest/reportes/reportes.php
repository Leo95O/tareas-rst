<?php

use App\Controllers\ReporteController;
use App\Middleware\AuthMiddleware;
use App\Middleware\ActiveUserMiddleware;
use App\Middleware\RolMiddleware;
use App\Constants\Roles; // Asegúrate de que la carpeta se llame 'Constants'

/** @var \Slim\Slim $app */
$container = $app->di;

// GRUPO REPORTES
// Protegido por Token (Auth) y Estado Activo (ActiveUser)
$app->group('/reportes', 
    AuthMiddleware::verificar($app), 
    ActiveUserMiddleware::verificar($app),
    function () use ($app, $container) {

    /**
     * 1. Dashboard Principal (Personalizado o Global)
     * GET /reportes/dashboard
     */
    $app->get('/dashboard', function () use ($app, $container) {
        // Obtenemos el usuario del contexto (inyectado por AuthMiddleware)
        $usuario = $app->usuario;
        
        // LÓGICA DE VISUALIZACIÓN:
        // - Si es rol USER (3), le pasamos su ID para que el repo filtre solo sus tareas.
        // - Si es ADMIN (1) o PM (2), pasamos NULL para que el repo traiga totales globales.
        $filtroId = ($usuario->rol_id === Roles::USER) ? $usuario->usuario_id : null;

        /** @var ReporteController $controller */
        $controller = $container->get(ReporteController::class);
        
        // Llamamos al método dashboard pasando el filtro decidido
        $controller->dashboard($filtroId);
    });

    /**
     * 2. Estadísticas Administrativas Avanzadas
     * GET /reportes/admin-stats
     * Solo accesible para Admin y Project Manager
     */
    $app->get('/admin-stats', 
        RolMiddleware::verificar($app, [Roles::ADMIN, Roles::PROJECT_MANAGER]), 
        function () use ($app, $container) {
            
            /** @var ReporteController $controller */
            $controller = $container->get(ReporteController::class);
            $controller->adminStats();
    });

});