<?php

namespace App\Services;

use App\Interfaces\Tarea\TareaServiceInterface;
use App\Interfaces\Tarea\TareaRepositoryInterface;
use App\Entities\Tarea;
use App\Validators\TareaValidator;
use App\Constants\EstadosTarea;
use App\Exceptions\ValidationException;

class TareaService implements TareaServiceInterface
{
    private $tareaRepository;

    public function __construct(TareaRepositoryInterface $tareaRepository)
    {
        $this->tareaRepository = $tareaRepository;
    }

    public function listarTareas($filtros = [])
    {
        return $this->tareaRepository->listar($filtros);
    }

    public function crearTarea($datos, $creadorId)
    {
        TareaValidator::validarCreacion($datos);

        $tarea = new Tarea();
        $tarea->tarea_titulo      = $datos['titulo'];
        $tarea->tarea_descripcion = $datos['descripcion'] ?? '';
        $tarea->fecha_limite      = $datos['fecha_limite'] ?? null;
        $tarea->prioridad_id      = (int) ($datos['prioridad_id'] ?? 2);
        $tarea->estado_id         = (int) ($datos['estado_id'] ?? EstadosTarea::POR_HACERR);
        $tarea->proyecto_id       = (int) $datos['proyecto_id'];
        $tarea->categoria_id      = isset($datos['categoria_id']) ? (int) $datos['categoria_id'] : null;
        
        // Asignación de propiedades corregidas
        $tarea->usuario_creador   = (int) $creadorId;

        if (isset($datos['usuario_asignado'])) {
            $tarea->usuario_asignado = (int) $datos['usuario_asignado'];
        }

        return $this->tareaRepository->crear($tarea);
    }

    public function editarTarea($id, $datos)
    {
        $tarea = $this->tareaRepository->obtenerPorId($id);
        if (!$tarea) {
            throw new ValidationException("La tarea solicitada no existe.");
        }

        TareaValidator::validarEdicion($datos);

        if (isset($datos['titulo']))       $tarea->tarea_titulo = $datos['titulo'];
        if (isset($datos['descripcion']))  $tarea->tarea_descripcion = $datos['descripcion'];
        if (isset($datos['fecha_limite'])) $tarea->fecha_limite = $datos['fecha_limite'];
        if (isset($datos['prioridad_id'])) $tarea->prioridad_id = (int) $datos['prioridad_id'];
        if (isset($datos['estado_id']))    $tarea->estado_id = (int) $datos['estado_id'];
        if (isset($datos['categoria_id'])) $tarea->categoria_id = (int) $datos['categoria_id'];
        
        if (array_key_exists('usuario_asignado', $datos)) {
            $tarea->usuario_asignado = !empty($datos['usuario_asignado']) ? (int) $datos['usuario_asignado'] : null;
        }

        return $this->tareaRepository->actualizar($tarea);
    }

    public function eliminarTarea($id)
    {
        $tarea = $this->tareaRepository->obtenerPorId($id);
        if (!$tarea) {
            throw new ValidationException("No se puede eliminar: La tarea no existe.");
        }
        return $this->tareaRepository->eliminar($id);
    }

    public function asignarTarea($tareaId, $usuarioId)
    {
        $tarea = $this->tareaRepository->obtenerPorId($tareaId);
        if (!$tarea) {
            throw new ValidationException("Error de asignación: La tarea no existe.");
        }

        // Validación contra la propiedad correcta
        if (!empty($tarea->usuario_asignado)) {
            throw new ValidationException("Esta tarea ya tiene un responsable asignado.");
        }

        $tarea->usuario_asignado = (int) $usuarioId;
        return $this->tareaRepository->actualizar($tarea);
    }
}