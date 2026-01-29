<?php

namespace App\Middleware;

use Slim\Slim;
use App\Utils\ApiResponse;
use App\Repositories\UsuarioRepository;

class ActiveUserMiddleware
{
    /**
     * Middleware de Seguridad Anti-Zombies.
     * Verifica en la Base de Datos que el usuario siga activo en tiempo real.
     */
    public static function verificar(Slim $app)
    {
        return function () use ($app) {
            $usuarioToken = isset($app->usuario) ? $app->usuario : null;

            if (!$usuarioToken) {
                return; 
            }

            try {
                $repo = $app->di->get(UsuarioRepository::class);
                
                // CORRECCIÓN AQUÍ:
                // Usamos 'sub' que es donde Auth.php guardó el ID (Estándar JWT)
                $idUsuario = $usuarioToken->sub; 

                $usuarioDb = $repo->obtenerPorId($idUsuario);

                // 4. Validaciones de Seguridad
                if (!$usuarioDb) {
                  echo ApiResponse::error("Credenciales revocadas. Usuario no encontrado.", []);
                  $app->stop();
                }

                if (!$usuarioDb->estaActivo()) {
                    echo ApiResponse::error("Sesión caducada. Tu cuenta ha sido desactivada.", []);
                    $app->stop();
                }

                // 5. Actualizar contexto con el objeto real de la BD
                $app->usuario = $usuarioDb;

            } catch (\Exception $e) {
                echo ApiResponse::error("Error de seguridad interno: " . $e->getMessage(), []);
                $app->stop();
            }
        };
    }
}