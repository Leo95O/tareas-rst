<?php

namespace App\Middleware;

use \Slim\Middleware;
use App\Utils\ApiResponse;
use App\Utils\Auth;
use App\Repositories\UsuarioRepository;

// Capa de protección previa al controlador
class AuthMiddleware extends Middleware
{
    public function call()
    {
        // Defino rutas públicas que no requieren autenticación
        $rutasPublicas = [
            '/usuarios/login',
            '/usuarios/registro',
            '/'
        ];

        // Obtener la ruta actual
        $rutaActual = $this->app->request->getPathInfo();

        // Si la ruta actual está en la lista blanca, dejamos pasar SIN verificar
        if (in_array($rutaActual, $rutasPublicas)) {
            $this->next->call();
            return;
        }

        $app = $this->app;
        $headers = $app->request->headers;
        $authHeader = $headers->get('Authorization');

        if (!$authHeader) {
            ApiResponse::error("Acceso denegado. Ruta protegida.", [], 401);
            return;
        }

        list($token) = sscanf($authHeader, 'Bearer %s');

        if (!$token) {
            ApiResponse::error("Formato de token inválido.", [], 401);
            return;
        }

        try {
            $decoded = Auth::verificarToken($token);
            $usuarioId = $decoded->data->id;

            $repo = new UsuarioRepository();
            $usuario = $repo->obtenerPorId($usuarioId);

            if (!$usuario || $usuario->usuario_token !== $token) {
                ApiResponse::error("Sesión inválida o expirada.", [], 401);
                return;
            }

            // Inyectamos el usuario
            $app->usuario = $usuario;

            $this->next->call();

        } catch (\Exception $e) {
            ApiResponse::error("Token inválido: " . $e->getMessage(), [], 401);
        }
    }
}