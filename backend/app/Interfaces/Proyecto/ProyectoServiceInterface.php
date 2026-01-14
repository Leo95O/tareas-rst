<?php
namespace App\Interfaces;

interface ProyectoServiceInterface
{
    public function listarProyectos($usuarioId, $rolId);
    public function crearProyecto($datos, $usuarioId);
    public function editarProyecto($id, $datos, $usuarioId, $rolId);
    public function eliminarProyecto($id, $usuarioId, $rolId);
}