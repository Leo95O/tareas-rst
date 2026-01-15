<?php

namespace App\Middleware;

use Slim\Slim;
use App\Utils\ApiResponse;
use App\Utils\Auth;
use Exception;

class AuthMiddleware
{
    // Definimos las rutas exactas que son públicas dentro de este módulo
    private static $rutasPublicas = [
        '/usuarios/login'
    ];

    public static function verificar(Slim $app)
    {
        return function () use ($app) {
            $req = $app->request;
            // Obtenemos la ruta relativa (resource URI)
            $rutaActual = $req->getResourceUri();

            // 1. VALIDACIÓN DE LISTA BLANCA (STRICT MODE)
            // Si la ruta es pública, interrumpimos el middleware y dejamos pasar.
            if (in_array($rutaActual, self::$rutasPublicas)) {
                return;
            }

            // --- DE AQUÍ PARA ABAJO, TODO ES PRIVADO ---

            // 2. Obtener header Authorization
            $authHeader = $req->headers->get('Authorization');

            if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                ApiResponse::error("Token no proporcionado o formato inválido.", [], 401);
                $app->stop();
            }

            $token = $matches[1];

            try {
                // 3. Validar Token
                $usuarioDecodificado = Auth::verificarToken($token);

                if (!$usuarioDecodificado) {
                    throw new Exception("Token inválido.");
                }

                // Inyectamos el usuario para que RolMiddleware lo consuma después
                $app->usuario = $usuarioDecodificado;

            } catch (Exception $e) {
                ApiResponse::error("Acceso denegado: " . $e->getMessage(), [], 401);
                $app->stop();
            }
        };
    }
}