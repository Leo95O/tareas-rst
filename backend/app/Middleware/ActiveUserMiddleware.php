<?php

namespace App\Middleware;

use Slim\Slim;
use App\Utils\ApiResponse;
use App\Repositories\UsuarioRepository;
// No necesitamos importar la Entidad EstadoUsuario aquí, 
// solo necesitamos saber si el método estaActivo() devuelve true/false.

class ActiveUserMiddleware
{
    /**
     * Middleware de Seguridad Anti-Zombies.
     * Verifica en la Base de Datos que el usuario siga activo en tiempo real.
     * * @param Slim $app
     * @return callable
     */
    public static function verificar(Slim $app)
    {
        return function () use ($app) {
            // 1. Verificar si hay un usuario autenticado por AuthMiddleware
            // (Si es una ruta pública, $app->usuario será null y saltamos esta validación)
            $usuarioToken = isset($app->usuario) ? $app->usuario : null;

            if (!$usuarioToken) {
                return; // Continuar, no aplica seguridad de estado si no hay login
            }

            try {
                // 2. Obtener la conexión a la BD mediante el Repositorio
                // Inyectamos la dependencia desde el contenedor de Slim
                $repo = $app->di->get(UsuarioRepository::class);
                
                // 3. Consultar la "Verdad Absoluta" en la BD
                // Usamos el ID que venía en el Token para buscar al usuario fresco
                $usuarioDb = $repo->obtenerPorId($usuarioToken->usuario_id);

                // 4. Validaciones de Seguridad
                if (!$usuarioDb) {
                    // El usuario fue borrado físicamente o no existe
                    ApiResponse::error("Credenciales revocadas. Usuario no encontrado.", [], 401);
                    $app->stop();
                }

                if (!$usuarioDb->estaActivo()) {
                    // El usuario existe pero fue desactivado (Soft Delete o Ban)
                    ApiResponse::error("Sesión caducada. Tu cuenta ha sido desactivada.", [], 401);
                    $app->stop();
                }

                // 5. (Opcional pero recomendado) Actualizar el contexto
                // Reemplazamos los datos viejos del token con los datos frescos de la BD
                // Así el controlador siguiente tendrá el Rol y Nombre actualizados.
                $app->usuario = $usuarioDb;

            } catch (\Exception $e) {
                // Si falla la BD, denegamos acceso por seguridad (Fail Close)
                ApiResponse::error("Error de seguridad interno: " . $e->getMessage(), [], 500);
                $app->stop();
            }
        };
    }
}