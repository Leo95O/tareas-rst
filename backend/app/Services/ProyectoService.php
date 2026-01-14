<?php

namespace App\Services;

use App\Interfaces\Proyecto\ProyectoServiceInterface;
use App\Interfaces\Proyecto\ProyectoRepositoryInterface;
use App\Entities\Proyecto;
use App\Utils\Crypto;
use App\Validators\ProyectoValidator;
use Exception;

class ProyectoService implements ProyectoServiceInterface
{
    private $proyectoRepository;

    public function __construct(ProyectoRepositoryInterface $repo)
    {
        $this->proyectoRepository = $repo;
    }

    // Se lista según el rol del usuario
    public function listarProyectos($usuarioActual = null)
    {
        // Si es usuario normal (rol 3), solo ver proyectos donde tenga tareas asignadas
        if ($usuarioActual && $usuarioActual->rol_id == 3) {
            $proyectos = $this->proyectoRepository->listarPorUsuario(
                $usuarioActual->usuario_id
            );
        } else {
            // Admin y PM ven todo. Pasamos IDs por si el repo requiere filtrado futuro
            $uid = $usuarioActual ? $usuarioActual->usuario_id : null;
            $rid = $usuarioActual ? $usuarioActual->rol_id : null;
            $proyectos = $this->proyectoRepository->listar($uid, $rid);
        }

        foreach ($proyectos as $p) {
            if (!empty($p->nombre_creador)) {
                $p->nombre_creador = Crypto::desencriptar($p->nombre_creador);
            }
        }

        return $proyectos;
    }

    public function obtenerProyectoPorId($id)
    {
        $proyecto = $this->proyectoRepository->obtenerPorId($id);

        if (!$proyecto) {
            throw new Exception("El proyecto no existe.");
        }
        return $proyecto;
    }

    public function crearProyecto($datos, $usuarioActual)
    {
        // Se verifican los permisos
        if ($usuarioActual->rol_id === 3) {
            throw new Exception("No tienes permisos para crear proyectos.");
        }

        // Se validan los datos previos
        ProyectoValidator::validarCreacion($datos);

        // Se instancia el proyecto y se asignan los datos
        $proyecto = new Proyecto();

        $proyecto->proyecto_nombre = $datos["nombre"];
        $proyecto->sucursal_id     = $datos["sucursal_id"];
        $proyecto->usuario_creador = $usuarioActual->usuario_id;

        $proyecto->proyecto_descripcion = $datos["descripcion"]  ?? "";
        $proyecto->estado_id            = $datos["estado_id"]    ?? 1;

        // Lógica de fechas
        $inicio = $datos["fecha_inicio"] ?? date("Y-m-d");
        $fin    = $datos["fecha_fin"]    ?? null;

        if ($fin && strtotime($inicio) > strtotime($fin)) {
            throw new Exception("La fecha de inicio ($inicio) no puede ser mayor a la fecha fin ($fin).");
        }

        $proyecto->fecha_inicio = $inicio;
        $proyecto->fecha_fin    = $fin;

        return $this->proyectoRepository->crear($proyecto);
    }

    public function editarProyecto($id, $datos, $usuarioActual)
    {
        if ($usuarioActual->rol_id === 3) {
            throw new Exception("No tienes permisos para editar proyectos.");
        }

        ProyectoValidator::validarEdicion($datos);

        $proyecto = $this->proyectoRepository->obtenerPorId($id);
        if (!$proyecto) {
            throw new Exception("El proyecto no existe.");
        }

        $proyecto->proyecto_nombre      = $datos["nombre"]      ?? $proyecto->proyecto_nombre;
        $proyecto->proyecto_descripcion = $datos["descripcion"] ?? $proyecto->proyecto_descripcion;
        $proyecto->sucursal_id          = $datos["sucursal_id"] ?? $proyecto->sucursal_id;
        $proyecto->estado_id            = $datos["estado_id"]   ?? $proyecto->estado_id;

        // Lógica de fechas para edición
        $nuevaInicio = $datos["fecha_inicio"] ?? $proyecto->fecha_inicio;
        $nuevaFin    = $datos["fecha_fin"]    ?? $proyecto->fecha_fin;

        if ($nuevaFin && strtotime($nuevaInicio) > strtotime($nuevaFin)) {
            throw new Exception("La fecha de inicio no puede ser posterior a la fecha de fin.");
        }

        $proyecto->fecha_inicio = $nuevaInicio;
        $proyecto->fecha_fin    = $nuevaFin;

        return $this->proyectoRepository->actualizar($proyecto);
    }

    // Soft Delete
    public function eliminarProyecto($id, $usuarioActual)
    {
        if ($usuarioActual->rol_id === 3) {
            throw new Exception("No tienes permisos para eliminar proyectos.");
        }

        return $this->proyectoRepository->eliminar($id, $usuarioActual->usuario_id);
    }
}