<?php
namespace App\Interfaces;

use App\Entities\Proyecto;

interface ProyectoRepositoryInterface
{
    public function listar($usuarioId, $rolId);
    public function listarPorUsuario($usuarioId);
    public function crear(Proyecto $proyecto);
    public function actualizar(Proyecto $proyecto);
    public function eliminar($proyectoId, $usuarioId);
    public function obtenerPorId($proyectoId);
    public function existeNombre($nombre, $excluirId = null);
}