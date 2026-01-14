<?php
namespace App\Interfaces;

use App\Entities\Tarea;

interface TareaRepositoryInterface
{
    public function listar($usuarioId, $rolId);
    public function listarBolsa();
    public function crear(Tarea $tarea);
    public function actualizar(Tarea $tarea);
    public function eliminar($id);
    public function obtenerPorId($id);
    public function asignarUsuario($tareaId, $usuarioId);
}