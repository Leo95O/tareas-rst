<?php

namespace App\Services;

use App\Interfaces\Usuario\UsuarioServiceInterface;
use App\Interfaces\Usuario\UsuarioRepositoryInterface;
use App\Interfaces\LoginGuard\LoginGuardServiceInterface;
use App\Entities\Usuario;
use App\Utils\Crypto;
use App\Constants\Estados;
use App\Exceptions\ValidationException;

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

    public function loginUsuario($correo, $password)
    {
        // 1. Check Anti-Fuerza Bruta
        $estadoSeguridad = $this->loginGuard->verificarSiPuedeEntrar($correo);

        // 2. Obtener usuario (Hidratado)
        $usuario = $this->usuarioRepository->obtenerPorCorreo($correo);
        
        $loginExitoso = false;

        // 3. Validar Password
        if ($usuario && password_verify($password, $usuario->usuario_password)) {
            $loginExitoso = true;
        }

        if ($loginExitoso) {
            // 4. SEGURIDAD CRÍTICA: Verificamos el estado ANTES de limpiar el historial.
            if (!$usuario->estaActivo()) {
                $this->loginGuard->procesarIntentoFallido($correo, $estadoSeguridad);
                throw new ValidationException("Tu cuenta está desactivada. Contacta al administrador.");
            }

            // 5. Solo si pasa TODO (Pass + Estado), limpiamos el historial
            $this->loginGuard->limpiarHistorial($correo);

            if (!empty($usuario->usuario_nombre)) {
                $usuario->usuario_nombre = Crypto::desencriptar($usuario->usuario_nombre);
            }
            
            return $usuario;

        } else {
            $this->loginGuard->procesarIntentoFallido($correo, $estadoSeguridad);
            throw new ValidationException("Credenciales incorrectas.");
        }
    }

    public function guardarTokenSesion($usuarioId, $token) 
    { 
        return $this->usuarioRepository->actualizarToken($usuarioId, $token); 
    }

    public function listarUsuariosAdmin($filtroRol = null) 
    { 
        return $this->usuarioRepository->listar($filtroRol); 
    }

    public function crearUsuarioAdmin($datos) 
    {
        if ($this->usuarioRepository->obtenerPorCorreo($datos['usuario_correo'])) { 
            throw new ValidationException("El correo ya existe."); 
        }

        $nuevo = new Usuario();
        $nuevo->usuario_nombre = Crypto::encriptar($datos['usuario_nombre']);
        $nuevo->usuario_correo = $datos['usuario_correo'];
        $nuevo->usuario_password = password_hash($datos['usuario_password'], PASSWORD_BCRYPT);
        $nuevo->rol_id = (int)$datos['rol_id'];
        $nuevo->usuario_estado = isset($datos['usuario_estado']) ? (int)$datos['usuario_estado'] : Estados::ACTIVO;

        return $this->usuarioRepository->crearUsuario($nuevo);
    }

    public function editarUsuarioAdmin($id, $datos) 
    {
        $usuarioEditar = $this->usuarioRepository->obtenerParaEditar($id);
        if (!$usuarioEditar) { 
            throw new ValidationException("Usuario no encontrado."); 
        }

        if (!empty($datos['usuario_nombre'])) { 
            $usuarioEditar->usuario_nombre = Crypto::encriptar($datos['usuario_nombre']); 
        }

        $usuarioEditar->usuario_correo = $datos['usuario_correo'] ?? $usuarioEditar->usuario_correo;
        $usuarioEditar->rol_id = isset($datos['rol_id']) ? (int)$datos['rol_id'] : $usuarioEditar->rol_id;
        
        if (isset($datos['usuario_estado'])) { 
            $usuarioEditar->usuario_estado = (int)$datos['usuario_estado']; 
        }

        return $this->usuarioRepository->actualizar($usuarioEditar);
    }

    public function eliminarUsuarioAdmin($id, $usuarioLogueadoId) 
    {
        if ((int)$id === (int)$usuarioLogueadoId) { 
            throw new ValidationException("No puedes eliminar tu propia cuenta."); 
        }
        return $this->usuarioRepository->eliminar($id);
    }
}