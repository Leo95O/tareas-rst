<?php

namespace App\Interfaces\Proyecto;

interface ProyectoServiceInterface
{
    public function listarProyectos($usuario);
    public function obtenerProyectoPorId($id);
    public function crearProyecto($datos, $usuario);
    public function editarProyecto($id, $datos, $usuario);
    public function eliminarProyecto($id, $usuario);
}