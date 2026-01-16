<?php

namespace App\Interfaces\Tarea;

interface TareaServiceInterface
{
    /**
     * Obtiene tareas según filtros.
     * @param array $filtros
     * @return array
     */
    public function listarTareas($filtros = []);

    /**
     * Crea una tarea validando reglas de negocio.
     * @param array $datos
     * @param int $creadorId ID del usuario que crea (auditoría)
     * @return int ID de la tarea
     */
    public function crearTarea($datos, $creadorId);

    /**
     * Edita una tarea.
     * @param int $id
     * @param array $datos
     * @return bool
     */
    public function editarTarea($id, $datos);

    /**
     * Elimina una tarea.
     * @param int $id
     * @return bool
     */
    public function eliminarTarea($id);

    /**
     * Asigna una tarea a un usuario (o se auto-asigna).
     * @param int $tareaId
     * @param int $usuarioId ID del usuario al que se asigna
     * @return bool
     */
    public function asignarTarea($tareaId, $usuarioId);
    
    // listarBolsa() se reemplaza por listarTareas(['usuario_asignado' => null])
}