<?php

namespace App\Services;

use App\Repositories\TareaRepository;
use App\Entities\Tarea;
use App\Utils\Crypto;
use Exception;

class TareaService
{
    private $tareaRepository;

    public function __construct()
    {
        // Inicializa el repositorio de tareas
        $this->tareaRepository = new TareaRepository();
    }

    // Lista las tareas aplicando filtro por rol y desencripta los nombres asignados
    public function listarTareas($usuario)
    {
        $tareas = $this->tareaRepository->listar($usuario->usuario_id, $usuario->rol_id);

        // Ajusta los nombres visibles de usuarios asignados
        foreach ($tareas as $tarea) {
            $tarea->nombre_asignado = !empty($tarea->nombre_asignado)
                ? Crypto::desencriptar($tarea->nombre_asignado)
                : "Sin asignar";
        }
        return $tareas;
    }

    public function crearTarea($datos, $usuarioActual)
    {
        $tarea = new Tarea();
        $tarea->tarea_titulo      = $datos['titulo'];

        $tarea->usuario_asignado  = ($usuarioActual->rol_id === 3)
            ? $usuarioActual->usuario_id
            : ($datos['usuario_asignado'] ?? null);
            
        $tarea->tarea_descripcion = $datos['descripcion']  ?? '';
        $tarea->fecha_limite      = $datos['fecha_limite'] ?? null;
        $tarea->prioridad_id      = $datos['prioridad_id'] ?? 2;
        $tarea->estado_id         = $datos['estado_id']    ?? 1;
        $tarea->proyecto_id       = $datos['proyecto_id'];
        $tarea->categoria_id      = $datos['categoria_id'] ?? null;
        $tarea->usuario_creador   = $usuarioActual->usuario_id;

        return $this->tareaRepository->crear($tarea);
    }

    public function editarTarea($id, $datos, $usuarioActual)
    {
        $tareaActual = $this->tareaRepository->obtenerPorId($id);
        if (!$tareaActual) {
            throw new Exception("La tarea no existe.");
        }

        // El usuario normal solo puede modificar tareas propias
        if ($usuarioActual->rol_id === 3) {
            $soyElAsignado = $tareaActual->usuario_asignado === $usuarioActual->usuario_id;
            $soyElCreador = $tareaActual->usuario_creador === $usuarioActual->usuario_id;

            if (!$soyElAsignado && !$soyElCreador) {
                throw new Exception("No tienes permiso para editar esta tarea.");
            }
        }

        // Solo Admin o PM pueden reasignar tareas
        if ($usuarioActual->rol_id !== 3) {
            if (array_key_exists('usuario_asignado', $datos)) {
                $tareaActual->usuario_asignado = $datos['usuario_asignado'];
            }
        }

        // Usuario normal (rol 3) solo puede cambiar el estado
        if ($usuarioActual->rol_id === 3) {
            // Solo permitir cambio de estado para usuario normal
            $tareaActual->estado_id = $datos['estado_id'] ?? $tareaActual->estado_id;
        } else {
            // Admin y PM pueden actualizar todos los campos
            $tareaActual->tarea_titulo      = $datos['titulo'] ?? $tareaActual->tarea_titulo;
            $tareaActual->tarea_descripcion = $datos['descripcion'] ?? $tareaActual->tarea_descripcion;
            $tareaActual->fecha_limite      = $datos['fecha_limite'] ?? $tareaActual->fecha_limite;
            $tareaActual->prioridad_id      = $datos['prioridad_id'] ?? $tareaActual->prioridad_id;
            $tareaActual->estado_id         = $datos['estado_id'] ?? $tareaActual->estado_id;
            $tareaActual->usuario_asignado  = $datos['usuario_asignado'] ?? $tareaActual->usuario_asignado;
        }

        return $this->tareaRepository->actualizar($tareaActual);
    }

    public function eliminarTarea($id, $usuarioActual)
    {
        $tarea = $this->tareaRepository->obtenerPorId($id);

        if (!$tarea) {
            throw new Exception("La tarea no existe o ya fue eliminada.");
        }

        // El usuario normal solo puede borrar tareas propias o asignadas
        if ($usuarioActual->rol_id === 3) {
            if (
                $tarea->usuario_creador != $usuarioActual->usuario_id &&
                $tarea->usuario_asignado != $usuarioActual->usuario_id
            ) {
                throw new Exception("No tienes permiso para eliminar esta tarea.");
            }
        }

        return $this->tareaRepository->eliminar($id);
    }

    // Lista las tareas sin asignar (Bolsa de Tareas)
    public function listarTareasBolsa()
    {
        $tareas = $this->tareaRepository->listarSinAsignar();

        // Las tareas de la bolsa no tienen usuario asignado
        foreach ($tareas as $tarea) {
            $tarea->nombre_asignado = "Sin asignar";
        }

        return $tareas;
    }

    // Permite a un usuario auto-asignarse una tarea de la bolsa
    public function autoAsignarTarea($tareaId, $usuario)
    {
        // Verificar que la tarea existe
        $tarea = $this->tareaRepository->obtenerPorId($tareaId);
        if (!$tarea) {
            throw new Exception("La tarea no existe.");
        }

        // Verificar que la tarea NO tiene usuario asignado
        if (!empty($tarea->usuario_asignado)) {
            throw new Exception("Esta tarea ya está asignada a otro usuario.");
        }

        // Asignar la tarea al usuario actual
        return $this->tareaRepository->asignarUsuario($tareaId, $usuario->usuario_id);
    }
}
