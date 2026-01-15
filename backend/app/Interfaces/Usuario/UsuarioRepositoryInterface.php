<?php

namespace App\Interfaces\Usuario;

use App\Entities\Usuario;

interface UsuarioRepositoryInterface
{
    public function obtenerPorCorreo($correo);
    public function obtenerPorId($id);    
    public function obtenerParaEditar($id);    
    public function listar($filtroRol = null);
    public function crearUsuario(Usuario $usuario);
    public function actualizar(Usuario $usuario);
    public function eliminar($id);
    public function actualizarToken($usuarioId, $token);
}