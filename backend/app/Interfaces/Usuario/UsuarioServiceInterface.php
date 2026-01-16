<?php

namespace App\Interfaces\Usuario;

interface UsuarioServiceInterface
{
    public function loginUsuario($correo, $password);
    public function guardarTokenSesion($usuarioId, $token);
    public function listarUsuariosAdmin($filtroRol = null);
    public function crearUsuarioAdmin($datos);
    public function editarUsuarioAdmin($id, $datos);
    public function eliminarUsuarioAdmin($id, $usuarioLogueadoId);
}