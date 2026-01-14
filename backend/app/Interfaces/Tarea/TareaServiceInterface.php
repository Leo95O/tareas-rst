<?php

namespace App\Interfaces\Tarea;

interface TareaServiceInterface
{
    public function listarTareas($usuario);
    public function crearTarea($datos, $usuarioActual);
    public function editarTarea($id, $datos, $usuarioActual);
    public function eliminarTarea($id, $usuarioActual);
    public function listarBolsa();
    public function asignarTarea($tareaId, $usuario);
}