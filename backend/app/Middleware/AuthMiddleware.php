<?php

namespace App\Middleware;

use Slim\Slim;
use App\Utils\ApiResponse;
use App\Utils\Auth;
use Exception;

class AuthMiddleware
{
    // Lista de rutas que NO requieren token
    private static $rutasPublicas = [
        '/usuarios/login', 
        // Eliminado: '/usuarios/registro' ya no es pública ni existe
    ];

    public static function verificar(Slim $app)
    {
        return function () use ($app) {
            $req = $app->request;
            $rutaActual = $req->getResourceUri();

            // 1. Permitir acceso si la ruta está en la lista blanca
            foreach (self::$rutasPublicas as $ruta) {
                // Verificamos si la ruta actual EMPIEZA con una ruta pública
                if (strpos($rutaActual, $ruta) === 0) {
                    return; // Pasa sin validar token
                }
            }

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

                $app->usuario = $usuarioDecodificado;

            } catch (Exception $e) {
                ApiResponse::error("Acceso denegado: " . $e->getMessage(), [], 401);
                $app->stop();
            }
        };
    }
}