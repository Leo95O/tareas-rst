<?php

namespace App\Services;

use App\Interfaces\Tarea\TareaServiceInterface;
use App\Interfaces\Tarea\TareaRepositoryInterface;
use App\Entities\Tarea;
use App\Utils\Crypto;
use Exception;

class TareaService implements TareaServiceInterface
{
    private $tareaRepository;

    public function __construct(TareaRepositoryInterface $tareaRepository)
    {
        $this->tareaRepository = $tareaRepository;
    }

    public function listarTareas($usuario)
    {
        $tareas = $this->tareaRepository->listar($usuario->usuario_id, $usuario->rol_id);

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
        
        // Si es usuario normal (3), se asigna a sí mismo. Si es Admin, toma el valor del formulario.
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

        // Validación de permisos para Rol 3 (Usuario)
        if ($usuarioActual->rol_id === 3) {
            $soyElAsignado = $tareaActual->usuario_asignado === $usuarioActual->usuario_id;
            $soyElCreador = $tareaActual->usuario_creador === $usuarioActual->usuario_id;

            if (!$soyElAsignado && !$soyElCreador) {
                throw new Exception("No tienes permiso para editar esta tarea.");
            }
        }

        // Lógica de actualización según Rol
        if ($usuarioActual->rol_id === 3) {
            // Usuario Normal: Solo puede cambiar el estado (avance)
            $tareaActual->estado_id = $datos['estado_id'] ?? $tareaActual->estado_id;
        } else {
            // Admin/PM: Puede editar todo
            $tareaActual->tarea_titulo      = $datos['titulo']           ?? $tareaActual->tarea_titulo;
            $tareaActual->tarea_descripcion = $datos['descripcion']      ?? $tareaActual->tarea_descripcion;
            $tareaActual->fecha_limite      = $datos['fecha_limite']     ?? $tareaActual->fecha_limite;
            $tareaActual->prioridad_id      = $datos['prioridad_id']     ?? $tareaActual->prioridad_id;
            $tareaActual->estado_id         = $datos['estado_id']        ?? $tareaActual->estado_id;
            
            // Asignación de usuario (maneja nulos o cambios)
            if (array_key_exists('usuario_asignado', $datos)) {
                $tareaActual->usuario_asignado = $datos['usuario_asignado'];
            }
        }

        return $this->tareaRepository->actualizar($tareaActual);
    }

    public function eliminarTarea($id, $usuarioActual)
    {
        $tarea = $this->tareaRepository->obtenerPorId($id);

        if (!$tarea) {
            throw new Exception("La tarea no existe o ya fue eliminada.");
        }

        // Validación de permisos para borrar
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

    public function listarBolsa()
    {
        $tareas = $this->tareaRepository->listarSinAsignar();

        foreach ($tareas as $tarea) {
            $tarea->nombre_asignado = "Sin asignar";
        }

        return $tareas;
    }

    public function asignarTarea($tareaId, $usuario)
    {
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