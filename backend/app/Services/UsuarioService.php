<?php

namespace App\Services;

use App\Interfaces\Usuario\UsuarioServiceInterface;
use App\Interfaces\Usuario\UsuarioRepositoryInterface;
use App\Interfaces\LoginGuard\LoginGuardServiceInterface;
use App\Entities\Usuario;
use App\Utils\Crypto;
use App\Constants\Roles;
use App\Constants\EstadoUsuario;
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
        $estadoSeguridad = $this->loginGuard->verificarSiPuedeEntrar($correo);

        $usuario = $this->usuarioRepository->obtenerPorCorreo($correo);
        $loginExitoso = false;

        if ($usuario && password_verify($password, $usuario->usuario_password)) {
            $loginExitoso = true;
        }

        if ($loginExitoso) {
            $this->loginGuard->limpiarHistorial($correo);

            if (!$usuario->estaActivo()) {
                throw new Exception("El usuario está desactivado o eliminado.");
            }

            $usuario->usuario_nombre = Crypto::desencriptar($usuario->usuario_nombre);
            return $usuario;

        } else {
            $this->loginGuard->procesarIntentoFallido($correo, $estadoSeguridad);
            throw new Exception("Credenciales incorrectas.");
        }
    }

    public function guardarTokenSesion($usuarioId, $token)
    {
        return $this->usuarioRepository->actualizarToken($usuarioId, $token);
    }

    // --- MÉTODOS DE ADMINISTRADOR (Única vía para crear usuarios) ---

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
        
        // El Admin decide el rol
        $nuevo->rol_id = $datos['rol_id'];

        // El Admin decide el estado (por defecto ACTIVO)
        $nuevo->usuario_estado = isset($datos['usuario_estado']) 
            ? $datos['usuario_estado'] 
            : EstadoUsuario::ACTIVO;

        return $this->usuarioRepository->crearUsuario($nuevo);
    }

    public function editarUsuarioAdmin($id, $datos)
    {
        $usuarioEditar = $this->usuarioRepository->obtenerParaEditar($id);

        if (!$usuarioEditar) {
            throw new Exception("Usuario no encontrado.");
        }

        if (!empty($datos['usuario_nombre'])) {
            $usuarioEditar->usuario_nombre = Crypto::encriptar($datos['usuario_nombre']);
        }

        $usuarioEditar->usuario_correo = isset($datos['usuario_correo']) ? $datos['usuario_correo'] : $usuarioEditar->usuario_correo;
        $usuarioEditar->rol_id         = isset($datos['rol_id']) ? $datos['rol_id'] : $usuarioEditar->rol_id;
        $usuarioEditar->usuario_estado = isset($datos['usuario_estado']) ? $datos['usuario_estado'] : $usuarioEditar->usuario_estado;

        return $this->usuarioRepository->actualizar($usuarioEditar);
    }

    public function eliminarUsuarioAdmin($id, $usuarioLogueadoId)
    {
        if ($id == $usuarioLogueadoId) {
            throw new Exception("No puedes eliminar tu propia cuenta.");
        }

        return $this->usuarioRepository->eliminar($id);
    }
}