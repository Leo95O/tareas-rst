<?php

namespace App\Services;

use App\Interfaces\Tarea\TareaServiceInterface;
use App\Interfaces\Tarea\TareaRepositoryInterface;
use App\Entities\Tarea;
use App\Validators\TareaValidator;
use App\Constans\EstadosTarea;
use Exception;

class TareaService implements TareaServiceInterface
{
    private $tareaRepository;

    public function __construct(TareaRepositoryInterface $tareaRepository)
    {
        $this->tareaRepository = $tareaRepository;
    }

    // LISTAR: Recibe filtros limpios, el Repo hace el trabajo sucio del SQL
    public function listarTareas($filtros = [])
    {
        return $this->tareaRepository->listar($filtros);
    }

    // CREAR: Lógica pura de negocio
    public function crearTarea($datos, $creadorId)
    {
        // 1. Validar Datos
        TareaValidator::validarCreacion($datos);

        // 2. Construir Objeto
        $tarea = new Tarea();
        $tarea->tarea_titulo      = $datos['titulo'];
        $tarea->tarea_descripcion = $datos['descripcion'] ?? '';
        $tarea->fecha_limite      = $datos['fecha_limite'] ?? null;
        $tarea->prioridad_id      = $datos['prioridad_id'] ?? 2; // Default: Media
        $tarea->estado_id         = $datos['estado_id']    ?? EstadosTarea::BACKLOG;
        $tarea->proyecto_id       = $datos['proyecto_id'];
        $tarea->categoria_id      = $datos['categoria_id'] ?? null;
        $tarea->usuario_creador_id = $creadorId; // Auditoría

        // Asignación: Si viene en el array, se asigna. Si no, queda null (Bolsa).
        // La lógica de "si soy usuario normal me asigno a mí mismo" la mueve el Controlador/Frontend.
        if (isset($datos['usuario_asignado'])) {
            $tarea->usuario_asignado_id = $datos['usuario_asignado'];
        }

        return $this->tareaRepository->crear($tarea);
    }

    // EDITAR: Sin IFs de roles. Solo reglas de negocio universales.
    public function editarTarea($id, $datos)
    {
        $tarea = $this->tareaRepository->obtenerPorId($id);
        if (!$tarea) {
            throw new Exception("La tarea no existe.");
        }

        // Validación de negocio (ej: no se puede editar una tarea finalizada hace 1 mes)
        // ... aquí iría esa lógica si existiera ...

        // Actualización Parcial (Patch)
        if (isset($datos['titulo']))      $tarea->tarea_titulo = $datos['titulo'];
        if (isset($datos['descripcion'])) $tarea->tarea_descripcion = $datos['descripcion'];
        if (isset($datos['fecha_limite'])) $tarea->fecha_limite = $datos['fecha_limite'];
        if (isset($datos['prioridad_id'])) $tarea->prioridad_id = $datos['prioridad_id'];
        if (isset($datos['estado_id']))    $tarea->estado_id = $datos['estado_id'];
        
        // Asignación explícita (incluyendo desasignar con null)
        if (array_key_exists('usuario_asignado', $datos)) {
            $tarea->usuario_asignado_id = $datos['usuario_asignado'];
        }

        return $this->tareaRepository->actualizar($tarea);
    }

    // ELIMINAR: Directo al grano
    public function eliminarTarea($id)
    {
        $tarea = $this->tareaRepository->obtenerPorId($id);
        if (!$tarea) {
            throw new Exception("La tarea no existe.");
        }

        return $this->tareaRepository->eliminar($id);
    }

    // ASIGNAR: Acción atómica
    public function asignarTarea($tareaId, $usuarioId)
    {
        $tarea = $this->tareaRepository->obtenerPorId($tareaId);
        if (!$tarea) {
            throw new Exception("La tarea no existe.");
        }

        // Regla de Negocio: No se puede robar una tarea ya asignada
        if (!empty($tarea->usuario_asignado_id)) {
            throw new Exception("Esta tarea ya está asignada. Primero debe liberarse.");
        }

        // Actualizamos el objeto en memoria y guardamos
        $tarea->usuario_asignado_id = $usuarioId;
        return $this->tareaRepository->actualizar($tarea);
    }
}