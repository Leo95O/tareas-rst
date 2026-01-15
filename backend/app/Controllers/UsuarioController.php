<?php

namespace App\Controllers;

use App\Interfaces\Usuario\UsuarioServiceInterface;
use App\Utils\ApiResponse;
use App\Utils\Auth;
use App\Validators\UsuarioValidator;

class UsuarioController
{
    private $usuarioService;

    public function __construct(UsuarioServiceInterface $service)
    {
        $this->usuarioService = $service;
    }

    // --- MÉTODOS PÚBLICOS ---

    public function login($datos)
    {
        try {
            UsuarioValidator::validarLogin($datos);
            
            $usuario = $this->usuarioService->loginUsuario(
                $datos['usuario_correo'],
                $datos['usuario_password']
            );

            $tokenJwt = Auth::generarToken($usuario);
            $this->usuarioService->guardarTokenSesion($usuario->usuario_id, $tokenJwt);

            $respuesta = [
                'usuario' => $usuario->toArray(),
                'token' => $tokenJwt
            ];

            ApiResponse::exito("Inicio de sesión exitoso.", $respuesta);
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // --- MÉTODOS DE ADMINISTRADOR ---

    public function listarTodo($filtroRol = null)
    {
        try {
            $lista = $this->usuarioService->listarUsuariosAdmin($filtroRol);
            
            $data = array_map(function ($u) {
                return $u->toArray();
            }, $lista);

            ApiResponse::exito("Lista de usuarios.", $data);
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function crearAdmin($datos)
    {
        try {
            UsuarioValidator::validarCreacionAdmin($datos);
            $id = $this->usuarioService->crearUsuarioAdmin($datos);
            ApiResponse::exito("Usuario creado por admin.", ['id' => $id]);
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function editarAdmin($id, $datos)
    {
        try {
            UsuarioValidator::validarEdicionAdmin($datos);
            $this->usuarioService->editarUsuarioAdmin($id, $datos);
            ApiResponse::exito("Usuario actualizado.");
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function eliminarAdmin($id, $usuarioLogueado)
    {
        try {
            $this->usuarioService->eliminarUsuarioAdmin($id, $usuarioLogueado->usuario_id);
            ApiResponse::exito("Usuario eliminado (Soft Delete).");
        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }
}