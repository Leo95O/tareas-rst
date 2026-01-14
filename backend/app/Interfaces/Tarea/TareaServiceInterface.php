<?php
namespace App\Interfaces;

interface TareaServiceInterface
{
    public function listarTareas($usuarioLogueado);
    public function listarBolsa();
    public function crearTarea($datos, $usuarioLogueado);
    public function editarTarea($id, $datos, $usuarioLogueado);
    public function eliminarTarea($id, $usuarioLogueado);
    public function asignarTarea($tareaId, $usuarioId);
}