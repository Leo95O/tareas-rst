<?php

namespace App\Services;

use App\Repositories\UsuarioRepository;
use App\Entities\Usuario;
use App\Utils\Crypto;
use Exception;
use App\Services\LoginGuardService;

class UsuarioService
{
    private $usuarioRepository;
    private $loginGuard;

    public function __construct()
    {
        $this->usuarioRepository = new UsuarioRepository();
        $this->loginGuard = new LoginGuardService();
    }

    // Registro de usuario público    
    public function registrarUsuario($datos)
    {
        // Valida que el correo no esté duplicado
        if ($this->usuarioRepository->obtenerPorCorreo($datos['usuario_correo'])) {
            throw new Exception("El correo electrónico ya está registrado.");
        }

        // Encripta el nombre y hashea la contraseña
        $nombreEncriptado = Crypto::encriptar($datos['usuario_nombre']);
        $passwordHash = password_hash($datos['usuario_password'], PASSWORD_BCRYPT);

        // Crea la entidad Usuario con rol por defecto
        $nuevoUsuario = new Usuario([
            'usuario_nombre' => $nombreEncriptado,
            'usuario_correo' => $datos['usuario_correo'],
            'usuario_password' => $passwordHash,
            'rol_id' => 3,
            'usuario_estado' => 1
        ]);

        return $this->usuarioRepository->crearUsuario($nuevoUsuario);
    }

    public function loginUsuario($correo, $password)
    {
        // Verifica si el usuario puede intentar iniciar sesión
        $estadoSeguridad = $this->loginGuard->verificarSiPuedeEntrar($correo);

        // Obtiene el usuario y valida credenciales
        $usuario = $this->usuarioRepository->obtenerPorCorreo($correo);
        $loginExitoso = false;

        if ($usuario && password_verify($password, $usuario->usuario_password)) {
            $loginExitoso = true;
        }

        // Procesa el resultado del login
        if ($loginExitoso) {
            // Limpia el historial de intentos fallidos
            $this->loginGuard->limpiarHistorial($correo);

            // Valida que el usuario esté activo
            if (!$usuario->estaActivo()) {
                throw new Exception("El usuario está desactivado o eliminado.");
            }

            // Desencripta el nombre para devolverlo al controller
            $usuario->usuario_nombre = Crypto::desencriptar($usuario->usuario_nombre);
            return $usuario;

        } else {
            // Registra el intento fallido
            $this->loginGuard->procesarIntentoFallido($correo, $estadoSeguridad);
            throw new Exception("Credenciales incorrectas.");
        }
    }

    public function guardarTokenSesion($usuarioId, $token)
    {
        return $this->usuarioRepository->actualizarToken($usuarioId, $token);
    }

    // Lógica de administración de usuarios
    public function listarUsuariosAdmin($usuarioLogueado, $filtroRol = null)
    {
        // Restringe el acceso a Administradores (1) y Project Managers (2)
        if ($usuarioLogueado->rol_id !== 1 && $usuarioLogueado->rol_id !== 2) {
            throw new Exception("No tienes permisos para listar usuarios.");
        }

        // Obtiene los usuarios con filtro opcional
        $usuarios = $this->usuarioRepository->listar($filtroRol);

        // Desencripta los nombres para visualización
        foreach ($usuarios as $u) {
            if (!empty($u->usuario_nombre)) {
                $u->usuario_nombre = Crypto::desencriptar($u->usuario_nombre);
            }
        }

        return $usuarios;
    }

    public function crearUsuarioAdmin($datos, $usuarioLogueado)
    {
        // Valida permisos de administrador
        if ($usuarioLogueado->rol_id !== 1) {
            throw new Exception("No tienes permisos.");
        }

        // Valida que el correo no exista
        if ($this->usuarioRepository->obtenerPorCorreo($datos['usuario_correo'])) {
            throw new Exception("El correo ya existe.");
        }

        $nuevo = new Usuario();

        // Encripta el nombre del usuario
        $nuevo->usuario_nombre = Crypto::encriptar($datos['usuario_nombre']);
        $nuevo->usuario_correo = $datos['usuario_correo'];
        $nuevo->usuario_password = password_hash($datos['usuario_password'], PASSWORD_BCRYPT);

        // Asigna el rol definido por el administrador
        $nuevo->rol_id = $datos['rol_id'];
        $nuevo->usuario_estado = 1;

        return $this->usuarioRepository->crearUsuario($nuevo);
    }

    public function editarUsuarioAdmin($id, $datos, $usuarioLogueado)
    {
        // Valida permisos de administrador
        if ($usuarioLogueado->rol_id !== 1) {
            throw new Exception("No tienes permisos.");
        }

        // Obtiene el usuario incluso si está inactivo
        $usuarioEditar = $this->usuarioRepository->obtenerParaEditar($id);

        if (!$usuarioEditar) {
            throw new Exception("Usuario no encontrado.");
        }

        // Actualiza el nombre solo si fue enviado
        if (!empty($datos['usuario_nombre'])) {
            $usuarioEditar->usuario_nombre = Crypto::encriptar($datos['usuario_nombre']);
        }

        // Mantiene valores anteriores si no vienen en la solicitud
        $usuarioEditar->usuario_correo = isset($datos['usuario_correo']) ? $datos['usuario_correo'] : $usuarioEditar->usuario_correo;
        $usuarioEditar->rol_id = isset($datos['rol_id']) ? $datos['rol_id'] : $usuarioEditar->rol_id;
        $usuarioEditar->usuario_estado = isset($datos['usuario_estado']) ? $datos['usuario_estado'] : $usuarioEditar->usuario_estado;

        return $this->usuarioRepository->actualizar($usuarioEditar);
    }

    public function eliminarUsuarioAdmin($id, $usuarioLogueado)
    {
        // Valida permisos de administrador
        if ($usuarioLogueado->rol_id !== 1) {
            throw new Exception("No tienes permisos.");
        }

        // Evita que el admin se elimine a sí mismo
        if ($id === $usuarioLogueado->usuario_id) {
            throw new Exception("No puedes eliminar tu propia cuenta.");
        }

        // Ejecuta el eliminado lógico
        return $this->usuarioRepository->eliminar($id);
    }
}