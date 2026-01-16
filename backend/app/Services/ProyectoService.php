<?php

namespace App\Services;

use App\Interfaces\Proyecto\ProyectoServiceInterface;
use App\Interfaces\Proyecto\ProyectoRepositoryInterface;
use App\Entities\Proyecto;
use App\Utils\Crypto;
use App\Validators\ProyectoValidator;
use App\Constans\EstadosProyecto; // ¡Uso de Constantes!
use Exception;

class ProyectoService implements ProyectoServiceInterface
{
    private $proyectoRepository;

    public function __construct(ProyectoRepositoryInterface $repo)
    {
        $this->proyectoRepository = $repo;
    }

    public function listarProyectos($filtros = [])
    {
        // El repositorio se encarga de la lógica de filtrado si viene en $filtros
        $proyectos = $this->proyectoRepository->listar($filtros);

        // Desencriptar nombres sensibles si es necesario (ej. nombre del creador si viniera encriptado)
        // Nota: En tu entidad Proyecto actual no veo 'nombre_creador', pero si el repo lo trae en el JOIN,
        // aquí es donde se desencripta.
        foreach ($proyectos as $p) {
            // Lógica de desencriptación si aplica
        }

        return $proyectos;
    }

    public function obtenerProyectoPorId($id)
    {
        $proyecto = $this->proyectoRepository->obtenerPorId($id);

        if (!$proyecto) {
            throw new Exception("El proyecto no existe o ha sido eliminado.");
        }
        return $proyecto;
    }

    public function crearProyecto(array $datos, $creadorId)
    {
        // 1. Validar Datos (Lógica de Negocio)
        ProyectoValidator::validarCreacion($datos);

        // 2. Validar Fechas
        $inicio = $datos["fecha_inicio"] ?? date("Y-m-d");
        $fin    = $datos["fecha_fin"]    ?? null;

        if ($fin && strtotime($inicio) > strtotime($fin)) {
            throw new Exception("La fecha de inicio no puede ser mayor a la fecha fin.");
        }

        // 3. Crear Entidad
        $proyecto = new Proyecto();
        $proyecto->proyecto_nombre      = $datos["nombre"];
        $proyecto->proyecto_descripcion = $datos["descripcion"] ?? "";
        $proyecto->sucursal_id          = $datos["sucursal_id"];
        $proyecto->usuario_creador      = $creadorId; // Auditoría
        
        // Estado por defecto (PENDIENTE) usando constante
        $proyecto->proyecto_estado      = isset($datos["estado_id"]) 
            ? $datos["estado_id"] 
            : EstadosProyecto::PENDIENTE;

        $proyecto->fecha_inicio = $inicio;
        $proyecto->fecha_fin    = $fin;

        return $this->proyectoRepository->crear($proyecto);
    }

    public function editarProyecto($id, array $datos)
    {
        // 1. Validar existencia
        $proyecto = $this->proyectoRepository->obtenerPorId($id);
        if (!$proyecto) {
            throw new Exception("El proyecto no existe.");
        }

        // 2. Validar Datos de Entrada
        // Si hay datos, validamos. Si es array vacío, el controller ya debió filtrar.
        if (!empty($datos)) {
            // ProyectoValidator::validarEdicion($datos); // Opcional si tienes validación parcial
        }

        // 3. Actualizar campos (Mapeo)
        if (isset($datos['nombre']))      $proyecto->proyecto_nombre = $datos['nombre'];
        if (isset($datos['descripcion'])) $proyecto->proyecto_descripcion = $datos['descripcion'];
        if (isset($datos['sucursal_id'])) $proyecto->sucursal_id = $datos['sucursal_id'];
        if (isset($datos['estado_id']))   $proyecto->proyecto_estado = $datos['estado_id'];

        // 4. Lógica de Fechas
        $nuevaInicio = $datos["fecha_inicio"] ?? $proyecto->fecha_inicio;
        $nuevaFin    = $datos["fecha_fin"]    ?? $proyecto->fecha_fin;

        if ($nuevaFin && strtotime($nuevaInicio) > strtotime($nuevaFin)) {
            throw new Exception("La fecha de inicio no puede ser posterior a la fecha de fin.");
        }

        $proyecto->fecha_inicio = $nuevaInicio;
        $proyecto->fecha_fin    = $nuevaFin;

        return $this->proyectoRepository->actualizar($proyecto);
    }

    public function eliminarProyecto($id)
    {
        $proyecto = $this->proyectoRepository->obtenerPorId($id);
        if (!$proyecto) {
            throw new Exception("El proyecto no existe.");
        }

        // Aquí podrías agregar reglas de negocio:
        // "No se puede eliminar un proyecto si tiene tareas en progreso"
        
        return $this->proyectoRepository->eliminar($id);
    }
}