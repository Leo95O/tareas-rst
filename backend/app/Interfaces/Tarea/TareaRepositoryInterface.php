<?php

namespace App\Interfaces\Tarea;

use App\Entities\Tarea;

interface TareaRepositoryInterface
{
    public function listar($usuarioId, $rolId);
    public function crear(Tarea $tarea);
    public function actualizar(Tarea $tarea);
    public function eliminar($tareaId);
    public function obtenerPorId($tareaId);
    public function listarSinAsignar(); 
    public function asignarUsuario($tareaId, $usuarioId);
}