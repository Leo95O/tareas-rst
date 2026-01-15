<?php

namespace App\Middleware;

use App\Utils\ApiResponse;
use Slim\Slim;

class RolMiddleware
{
    // AHORA: Recibimos $app explícitamente, igual que en AuthMiddleware
    public static function verificar(Slim $app, $rolesPermitidos = [])
    {
        return function () use ($app, $rolesPermitidos) {
            // Eliminamos: $app = Slim::getInstance();

            // 1. Obtener usuario (inyectado previamente por AuthMiddleware)
            $usuario = isset($app->usuario) ? $app->usuario : null;

            if (!$usuario) {
                ApiResponse::error("Sesión no válida o expirada.", [], 401);
                $app->stop();
            }

            // 2. Verificar Rol
            if (!in_array($usuario->rol_id, $rolesPermitidos)) {
                ApiResponse::error("Acceso denegado. Permisos insuficientes.", [], 403);
                $app->stop();
            }
        };
    }
}