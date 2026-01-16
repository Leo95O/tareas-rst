<?php

namespace App\Interfaces\Tarea;

use App\Entities\Tarea;

interface TareaRepositoryInterface
{
    /**
     * Lista tareas aplicando filtros dinámicos.
     * @param array $filtros (ej: ['usuario_asignado' => 1, 'proyecto_id' => 5])
     * @return Tarea[] Array de objetos hidratados
     */
    public function listar($filtros = []);

    /**
     * Obtiene una tarea por ID con todas sus relaciones hidratadas.
     * @param int $id
     * @return Tarea|null
     */
    public function obtenerPorId($id);

    /**
     * Inserta una nueva tarea.
     * @param Tarea $tarea
     * @return int|false ID de la tarea creada
     */
    public function crear(Tarea $tarea);

    /**
     * Actualiza una tarea existente.
     * @param Tarea $tarea
     * @return bool
     */
    public function actualizar(Tarea $tarea);

    /**
     * Realiza borrado (soft delete) de una tarea.
     * @param int $id
     * @return bool
     */
    public function eliminar($id);

    // Nota: 'listarSinAsignar' se puede cubrir con listar(['usuario_asignado' => null])
    // Nota: 'asignarUsuario' se puede cubrir con actualizar($tarea)
    // Pero si prefieres métodos explícitos para operaciones atómicas, puedes dejarlos.
    // Por limpieza, recomiendo usar los genéricos, pero mantendré asignarUsuario si es una acción muy específica.
}