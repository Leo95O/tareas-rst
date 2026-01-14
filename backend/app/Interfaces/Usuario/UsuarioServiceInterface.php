<?php

namespace App\Interfaces\Usuario;

interface UsuarioServiceInterface
{

    public function registrarUsuario($datos);
    public function loginUsuario($correo, $password);
    public function guardarTokenSesion($id, $token);
    public function listarUsuariosAdmin($usuarioLogueado, $filtroRol);
    public function crearUsuarioAdmin($datos, $usuarioLogueado);
    public function editarUsuarioAdmin($id, $datos, $usuarioLogueado);
    public function eliminarUsuarioAdmin($id, $usuarioLogueado);
}