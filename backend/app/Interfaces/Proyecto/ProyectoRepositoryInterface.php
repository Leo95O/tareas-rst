<?php

namespace App\Interfaces\Proyecto;

use App\Entities\Proyecto;

interface ProyectoRepositoryInterface
{
    public function listar($filtros = []);
    public function obtenerPorId($id);
    public function crear(Proyecto $proyecto);
    public function actualizar(Proyecto $proyecto);
    public function eliminar($id);
}