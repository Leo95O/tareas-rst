<?php

namespace App\Interfaces\Proyecto;

interface ProyectoServiceInterface
{
    public function listarProyectos($filtros = []);
    public function crearProyecto(array $datos, $creadorId);
    public function obtenerProyectoPorId($id);
    public function editarProyecto($id, array $datos);
    public function eliminarProyecto($id);
}