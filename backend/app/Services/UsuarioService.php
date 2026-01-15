<?php

namespace App\Services;

use App\Interfaces\Usuario\UsuarioServiceInterface;
use App\Interfaces\Usuario\UsuarioRepositoryInterface;
use App\Interfaces\LoginGuard\LoginGuardServiceInterface;
use App\Entities\Usuario;
use App\Entities\EstadoUsuario; // La Entidad para crear objetos
use App\Utils\Crypto;
use App\Constans\Roles;   // Las Constantes
use App\Constans\Estados; // Las Constantes
use Exception;

class UsuarioService implements UsuarioServiceInterface
{
    private $usuarioRepository;
    private $loginGuard;

    public function __construct(
        UsuarioRepositoryInterface $usuarioRepository,
        LoginGuardServiceInterface $loginGuard
    ) {
        $this->usuarioRepository = $usuarioRepository;
        $this->loginGuard = $loginGuard;
    }

    // --- MÉTODOS PÚBLICOS ---

    public function loginUsuario($correo, $password)
    {
        // 1. Verificar si la IP/Usuario está bloqueada por fuerza bruta
        $estadoSeguridad = $this->loginGuard->verificarSiPuedeEntrar($correo);

        // 2. Obtener usuario (El repositorio ya lo hidrata con Rol y Estado)
        $usuario = $this->usuarioRepository->obtenerPorCorreo($correo);
        
        $loginExitoso = false;

        // 3. Verificar Contraseña
        if ($usuario && password_verify($password, $usuario->usuario_password)) {
            $loginExitoso = true;
        }

        if ($loginExitoso) {
            // 4. SEGURIDAD CRÍTICA: Verificar Estado
            // Usamos el método de negocio de la Entidad, que consulta el objeto EstadoUsuario
            if (!$usuario->estaActivo()) {
                // Importante: No limpiamos intentos fallidos si está inactivo, para no dar pistas.
                throw new Exception("Tu cuenta está desactivada. Contacta al administrador.");
            }

            // 5. Todo OK: Limpiar historial de fallos y devolver usuario
            $this->loginGuard->limpiarHistorial($correo);

            // Desencriptar nombre para el frontend
            if (!empty($usuario->usuario_nombre)) {
                $usuario->usuario_nombre = Crypto::desencriptar($usuario->usuario_nombre);
            }
            
            return $usuario;

        } else {
            // 6. Fallo: Registrar intento y lanzar error genérico
            $this->loginGuard->procesarIntentoFallido($correo, $estadoSeguridad);
            throw new Exception("Credenciales incorrectas.");
        }
    }

    public function guardarTokenSesion($usuarioId, $token)
    {
        return $this->usuarioRepository->actualizarToken($usuarioId, $token);
    }

    // --- MÉTODOS DE ADMINISTRADOR ---

    public function listarUsuariosAdmin($filtroRol = null)
    {
        $usuarios = $this->usuarioRepository->listar($filtroRol);

        foreach ($usuarios as $u) {
            if (!empty($u->usuario_nombre)) {
                $u->usuario_nombre = Crypto::desencriptar($u->usuario_nombre);
            }
        }

        return $usuarios;
    }

    public function crearUsuarioAdmin($datos)
    {
        if ($this->usuarioRepository->obtenerPorCorreo($datos['usuario_correo'])) {
            throw new Exception("El correo ya existe.");
        }

        $nuevo = new Usuario();

        $nuevo->usuario_nombre   = Crypto::encriptar($datos['usuario_nombre']);
        $nuevo->usuario_correo   = $datos['usuario_correo'];
        $nuevo->usuario_password = password_hash($datos['usuario_password'], PASSWORD_BCRYPT);
        
        // Asignación de IDs directos (Foreign Keys)
        $nuevo->rol_id = $datos['rol_id'];
        
        // Por defecto ACTIVO si no se envía nada
        $nuevo->usuario_estado = isset($datos['usuario_estado']) 
            ? $datos['usuario_estado'] 
            : Estados::ACTIVO;

        return $this->usuarioRepository->crearUsuario($nuevo);
    }

    public function editarUsuarioAdmin($id, $datos)
    {
        // Obtenemos el usuario existente
        $usuarioEditar = $this->usuarioRepository->obtenerParaEditar($id);

        if (!$usuarioEditar) {
            throw new Exception("Usuario no encontrado.");
        }

        // Actualizamos campos solo si vienen en el array
        if (!empty($datos['usuario_nombre'])) {
            $usuarioEditar->usuario_nombre = Crypto::encriptar($datos['usuario_nombre']);
        }

        $usuarioEditar->usuario_correo = $datos['usuario_correo'] ?? $usuarioEditar->usuario_correo;
        $usuarioEditar->rol_id         = $datos['rol_id'] ?? $usuarioEditar->rol_id;
        
        // Actualizamos estado si viene definido
        if (isset($datos['usuario_estado'])) {
            $usuarioEditar->usuario_estado = $datos['usuario_estado'];
        }

        return $this->usuarioRepository->actualizar($usuarioEditar);
    }

    public function eliminarUsuarioAdmin($id, $usuarioLogueadoId)
    {
        if ($id == $usuarioLogueadoId) {
            throw new Exception("No puedes eliminar tu propia cuenta.");
        }

        // Soft Delete (El repositorio se encarga de ponerlo en INACTIVO)
        return $this->usuarioRepository->eliminar($id);
    }
}