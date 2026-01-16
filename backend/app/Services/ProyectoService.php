<?php

namespace App\Services;

use App\Interfaces\Proyecto\ProyectoServiceInterface;
use App\Interfaces\Proyecto\ProyectoRepositoryInterface;
use App\Entities\Proyecto;
use App\Validators\ProyectoValidator;
use App\Constants\EstadosProyecto;
use App\Exceptions\ValidationException;

class ProyectoService implements ProyectoServiceInterface
{
    private $proyectoRepository;

    public function __construct(ProyectoRepositoryInterface $repo)
    {
        $this->proyectoRepository = $repo;
    }

    public function listarProyectos($filtros = [])
    {
        return $this->proyectoRepository->listar($filtros);
    }

    public function obtenerProyectoPorId($id)
    {
        $proyecto = $this->proyectoRepository->obtenerPorId($id);

        if (!$proyecto) {
            throw new ValidationException("El proyecto solicitado no existe o ha sido eliminado.");
        }
        return $proyecto;
    }

    public function crearProyecto(array $datos, $creadorId)
    {
        ProyectoValidator::validarCreacion($datos);

        $inicio = $datos["fecha_inicio"] ?? date("Y-m-d");
        $fin    = $datos["fecha_fin"]    ?? null;

        if ($fin && strtotime($inicio) > strtotime($fin)) {
            throw new ValidationException("La fecha de inicio no puede ser posterior a la fecha de finalización.");
        }

        $proyecto = new Proyecto();
        $proyecto->proyecto_nombre      = $datos["nombre"];
        $proyecto->proyecto_descripcion = $datos["descripcion"] ?? "";
        $proyecto->sucursal_id          = $datos["sucursal_id"];
        $proyecto->usuario_creador       = $creadorId;
        
        $proyecto->proyecto_estado = isset($datos["estado_id"]) 
            ? (int) $datos["estado_id"] 
            : EstadosProyecto::ACTIVO;

        $proyecto->fecha_inicio = $inicio;
        $proyecto->fecha_fin    = $fin;

        return $this->proyectoRepository->crear($proyecto);
    }

    public function editarProyecto($id, array $datos)
    {
        $proyecto = $this->proyectoRepository->obtenerPorId($id);
        if (!$proyecto) {
            throw new ValidationException("No se puede editar: El proyecto no existe.");
        }

        ProyectoValidator::validarEdicion($datos);

        if (isset($datos['nombre']))      $proyecto->proyecto_nombre = $datos['nombre'];
        if (isset($datos['descripcion'])) $proyecto->proyecto_descripcion = $datos['descripcion'];
        if (isset($datos['sucursal_id'])) $proyecto->sucursal_id = (int) $datos['sucursal_id'];
        if (isset($datos['estado_id']))   $proyecto->proyecto_estado = (int) $datos['estado_id'];

        $nuevaInicio = $datos["fecha_inicio"] ?? $proyecto->fecha_inicio;
        $nuevaFin    = $datos["fecha_fin"]    ?? $proyecto->fecha_fin;

        if ($nuevaFin && strtotime($nuevaInicio) > strtotime($nuevaFin)) {
            throw new ValidationException("Error en fechas: El inicio no puede ser posterior al fin.");
        }

        $proyecto->fecha_inicio = $nuevaInicio;
        $proyecto->fecha_fin    = $nuevaFin;

        return $this->proyectoRepository->actualizar($proyecto);
    }

    public function eliminarProyecto($id)
    {
        $proyecto = $this->proyectoRepository->obtenerPorId($id);
        if (!$proyecto) {
            throw new ValidationException("No se puede eliminar: El proyecto no existe.");
        }

        return $this->proyectoRepository->eliminar($id);
    }
}