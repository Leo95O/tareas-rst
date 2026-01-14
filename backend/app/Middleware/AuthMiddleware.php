<?php

namespace App\Middleware;

use \Slim\Middleware;
use App\Utils\ApiResponse;
use App\Utils\Auth;
// Borramos "use App\Repositories\UsuarioRepository;" porque ya no la instanciamos directamente
use App\Interfaces\UsuarioRepositoryInterface;

// Capa de protección previa al controlador
class AuthMiddleware extends Middleware
{
    private $usuarioRepo;

    // 1. INYECCIÓN DE DEPENDENCIA (CORRECTO)
    public function __construct(UsuarioRepositoryInterface $repo)
    {
        $this->usuarioRepo = $repo;
    }

    public function call()
    {
        $rutasPublicas = [
            '/usuarios/login',
            '/usuarios/registro',
            '/'
        ];

        $rutaActual = $this->app->request->getPathInfo();

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

            // --- AQUÍ ESTABA EL ERROR ---
            // Antes tenías: $repo = new UsuarioRepository(); (ESTO FALLARÍA)
            
            // AHORA: Usamos la propiedad que inyectamos en el constructor
            $usuario = $this->usuarioRepo->obtenerPorId($usuarioId);

            if (!$usuario || $usuario->usuario_token !== $token) {
                ApiResponse::error("Sesión inválida o expirada.", [], 401);
                return;
            }

            // Inyectamos el usuario en la app para que otros controladores lo usen
            $app->usuario = $usuario;

            $this->next->call();

        } catch (\Exception $e) {
            ApiResponse::error("Token inválido: " . $e->getMessage(), [], 401);
        }
    }
}