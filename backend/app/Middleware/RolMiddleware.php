<?php

namespace App\Middleware;

use App\Utils\ApiResponse;
use \Slim\Slim;

class RolMiddleware
{
    // Retorna un middleware para validar roles permitidos
    public static function verificar($rolesPermitidos = [])
    {
        return function () use ($rolesPermitidos) {
            $app = Slim::getInstance();

            // Obtiene el usuario inyectado por el AuthMiddleware
            $usuario = isset($app->usuario) ? $app->usuario : null;

            if (!$usuario) {
                // Bloquea el acceso si no hay usuario autenticado
                ApiResponse::error("Error de seguridad: Usuario no identificado.", [], 401);
                return;
            }

            // Verifica si el rol del usuario está permitido
            if (!in_array($usuario->rol_id, $rolesPermitidos)) {
                ApiResponse::error("Acceso denegado. No tienes permisos suficientes para realizar esta acción.", [], 403);
            }

            // Continúa la ejecución si pasa la validación
        };
    }
}
