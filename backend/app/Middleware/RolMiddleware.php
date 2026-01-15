<?php

namespace App\Middleware;

use App\Utils\ApiResponse;
use Slim\Slim;

class RolMiddleware
{

    public static function verificar($rolesPermitidos = [])
    {
        return function () use ($rolesPermitidos) {
            $app = Slim::getInstance();

            // 1. Obtener usuario inyectado por AuthMiddleware
            $usuario = isset($app->usuario) ? $app->usuario : null;

            if (!$usuario) {
                // Retorna 401 Unauthorized y DETIENE la ejecución
                ApiResponse::error("Sesión no válida o expirada.", [], 401);
                $app->stop(); // ¡Vital en Slim 2!
            }

            // 2. Verificar Rol
            if (!in_array($usuario->rol_id, $rolesPermitidos)) {
                // Retorna 403 Forbidden y DETIENE la ejecución
                ApiResponse::error("Acceso denegado. Permisos insuficientes.", [], 403);
                $app->stop(); // ¡Vital en Slim 2!
            }

            // Si pasa, Slim continúa automáticamente al siguiente callable
        };
    }
}