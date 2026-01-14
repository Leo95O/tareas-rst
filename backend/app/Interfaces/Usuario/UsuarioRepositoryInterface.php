<?php
namespace App\Interfaces;

use App\Entities\Usuario;

interface UsuarioRepositoryInterface
{
    public function listar();
    public function obtenerPorId($id);
    public function buscarPorEmail($email);
    public function crear(Usuario $usuario);
    public function actualizar(Usuario $usuario);
    public function eliminar($id);
    public function actualizarToken($usuarioId, $token);
}