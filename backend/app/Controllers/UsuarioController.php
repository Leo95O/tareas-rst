<?php

namespace App\Controllers;

use App\Interfaces\Usuario\UsuarioServiceInterface;
use App\Utils\ApiResponse;
use App\Utils\Auth;
use App\Validators\UsuarioValidator;
use App\Exceptions\ValidationException;
use Exception;

class UsuarioController
{
    private $usuarioService;

    public function __construct(UsuarioServiceInterface $service)
    {
        $this->usuarioService = $service;
    }

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

            echo ApiResponse::exito("Inicio de sesión exitoso.", $respuesta);
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en UsuarioController::login: " . $e->getMessage());
            echo ApiResponse::error("Ocurrió un error interno en el servidor.");
        }
    }

    public function listarTodo($filtroRol = null)
    {
        try {
            $lista = $this->usuarioService->listarUsuariosAdmin($filtroRol);
            
            $data = array_map(function ($u) {
                return $u->toArray();
            }, $lista);

            echo ApiResponse::exito("Lista de usuarios obtenida.", $data);
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en UsuarioController::listarTodo: " . $e->getMessage());
            echo ApiResponse::error("No se pudo recuperar la lista de usuarios.");
        }
    }

    public function crearAdmin($datos)
    {
        try {
            UsuarioValidator::validarCreacionAdmin($datos);
            $id = $this->usuarioService->crearUsuarioAdmin($datos);
            echo ApiResponse::exito("Usuario creado correctamente.", ['id' => $id]);
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en UsuarioController::crearAdmin: " . $e->getMessage());
            echo ApiResponse::error("Error al intentar crear el usuario.");
        }
    }

    public function editarAdmin($id, $datos)
    {
        try {
            if (empty($id)) {
                throw new ValidationException("El ID del usuario es obligatorio.");
            }

            UsuarioValidator::validarEdicionAdmin($datos);
            $this->usuarioService->editarUsuarioAdmin($id, $datos);
            echo ApiResponse::exito("Usuario actualizado correctamente.");
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en UsuarioController::editarAdmin: " . $e->getMessage());
            echo ApiResponse::error("Ocurrió un error al actualizar el usuario.");
        }
    }

    public function eliminarAdmin($id, $usuarioLogueado)
    {
        try {
            if (empty($id)) {
                throw new ValidationException("ID de usuario no proporcionado.");
            }

            $this->usuarioService->eliminarUsuarioAdmin($id, $usuarioLogueado->usuario_id);
            echo ApiResponse::exito("Usuario eliminado correctamente.");
        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (Exception $e) {
            error_log("Error en UsuarioController::eliminarAdmin: " . $e->getMessage());
            echo ApiResponse::error("No se pudo completar la eliminación del usuario.");
        }
    }
}