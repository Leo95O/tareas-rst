<?php

namespace App\Interfaces\Usuario;

use App\Entities\Usuario;

interface UsuarioRepositoryInterface
{

    public function obtenerPorCorreo($correo);
    public function actualizarToken($usuarioId, $token);
    public function obtenerPorId($id); 
    public function obtenerParaEditar($id);     
    public function listarTodos(); 
    public function listar($rolId = null); 
    public function crearUsuario(Usuario $usuario);
    public function actualizar(Usuario $usuario);
    public function eliminar($id);
}