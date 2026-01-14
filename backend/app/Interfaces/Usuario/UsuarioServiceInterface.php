<?php
namespace App\Interfaces;

interface UsuarioServiceInterface
{
    public function listarUsuarios();
    public function crearUsuario($datos);
    public function editarUsuario($id, $datos);
    public function eliminarUsuario($id);
    public function obtenerPorId($id);
}