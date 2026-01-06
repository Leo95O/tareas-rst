<?php

namespace App\Controllers;

use App\Services\UsuarioService;
use App\Utils\ApiResponse;
use App\Utils\Auth;
use App\Validators\UsuarioValidator;
use \Slim\Slim;

class UsuarioController
{
    private $usuarioService;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
    }

    // MÉTODOS PÚBLICOS (Registro y Login)
    public function registrar()
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);

            // Validar datos con el Validator
            UsuarioValidator::validarRegistro($datos);

            // Registrar (Por defecto Rol 3)
            $nuevoId = $this->usuarioService->registrarUsuario($datos);

            ApiResponse::exito("Usuario registrado correctamente.", ['id' => $nuevoId]);

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // Metodo login() del frontend
    public function login()
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);

            UsuarioValidator::validarLogin($datos);

            // Validar credenciales (incluye lógica de bloqueo)
            $usuario = $this->usuarioService->loginUsuario(
                $datos['usuario_correo'],
                $datos['usuario_password']
            );

            // Generar Token JWT
            $tokenJwt = Auth::generarToken($usuario);

            // Guardar sesión en BD
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

    // MÉTODOS DE ADMINISTRADOR (Gestión)
    // Metodo getUsers() del frontend
    public function listarTodo()
    {
        try {
            $app = Slim::getInstance();
            $usuarioLogueado = $app->usuario; // Inyectado por Middleware

            // Filtro opcional por URL (ej: ?rol_id=2)
            $filtroRol = $app->request->get('rol_id');

            $lista = $this->usuarioService->listarUsuariosAdmin($usuarioLogueado, $filtroRol);

            // Mapear a array
            $data = array_map(function ($u) {
                return $u->toArray();
            }, $lista);

            ApiResponse::exito("Lista de usuarios.", $data);

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // Metodo createUser() del frontend
    public function crearAdmin()
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);
            $usuarioLogueado = $app->usuario;

            UsuarioValidator::validarCreacionAdmin($datos);

            $id = $this->usuarioService->crearUsuarioAdmin($datos, $usuarioLogueado);

            ApiResponse::exito("Usuario creado por admin.", ['id' => $id]);

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // Metodo updateUser() del frontend
    public function editarAdmin($id)
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);
            $usuarioLogueado = $app->usuario;

            UsuarioValidator::validarEdicionAdmin($datos);

            $this->usuarioService->editarUsuarioAdmin($id, $datos, $usuarioLogueado);

            ApiResponse::exito("Usuario actualizado.");

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    // Metodo deleteUser() del frontend
    public function eliminarAdmin($id)
    {
        try {
            $app = Slim::getInstance();
            $usuarioLogueado = $app->usuario;

            $this->usuarioService->eliminarUsuarioAdmin($id, $usuarioLogueado);

            ApiResponse::exito("Usuario eliminado (Soft Delete).");

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }
}