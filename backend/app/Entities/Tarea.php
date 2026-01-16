<?php

namespace App\Entities;

use App\Constants\EstadosTarea;

class Tarea
{
    public $tarea_id;
    public $tarea_titulo;
    public $tarea_descripcion;
    public $fecha_limite;
    
    // IDs planos (Foreign Keys) - Sincronizados con la BD
    public $prioridad_id;
    public $estado_id;
    public $proyecto_id;
    public $categoria_id;
    public $usuario_asignado; // Nombre exacto en la tabla 'tareas'
    public $usuario_creador;  // Nombre exacto en la tabla 'tareas'
    
    // Objetos Hidratados
    public $estado;
    public $prioridad;
    public $categoria;
    public $asignado;
    public $proyecto;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->tarea_id = $data['tarea_id'] ?? null;
            $this->tarea_titulo = $data['tarea_titulo'] ?? null;
            $this->tarea_descripcion = $data['tarea_descripcion'] ?? null;
            $this->fecha_limite = $data['fecha_limite'] ?? null;
            
            $this->prioridad_id = $data['prioridad_id'] ?? null;
            $this->estado_id = $data['estado_id'] ?? null;
            $this->proyecto_id = $data['proyecto_id'] ?? null;
            $this->categoria_id = $data['categoria_id'] ?? null;
            
            // Mapeo directo desde los nombres de la BD
            $this->usuario_asignado = $data['usuario_asignado'] ?? null;
            $this->usuario_creador = $data['usuario_creador'] ?? null;
        }
    }

    public function setEstado(EstadoTarea $estado) { $this->estado = $estado; }
    public function setPrioridad(PrioridadTarea $prioridad) { $this->prioridad = $prioridad; }
    public function setCategoria(CategoriaTarea $categoria) { $this->categoria = $categoria; }

    public function estaFinalizada() {
        return (int)$this->estado_id === EstadosTarea::COMPLETADA; // Sincronizado con dump BD
    }

    public function toArray()
    {
        return [
            'id'          => $this->tarea_id,
            'titulo'      => $this->tarea_titulo,
            'descripcion' => $this->tarea_descripcion,
            'fecha_limite'=> $this->fecha_limite,
            'estado'      => $this->estado ? [
                'id' => $this->estado->estado_id,
                'nombre' => $this->estado->estado_nombre
            ] : ['id' => $this->estado_id],
            'prioridad'   => $this->prioridad ? [
                'id' => $this->prioridad->prioridad_id,
                'nombre' => $this->prioridad->prioridad_nombre
            ] : ['id' => $this->prioridad_id],
            'categoria'   => $this->categoria ? [
                'id' => $this->categoria->categoria_id,
                'nombre' => $this->categoria->categoria_nombre
            ] : ($this->categoria_id ? ['id' => $this->categoria_id] : null),
            'proyecto_id' => $this->proyecto_id,
            'asignado_id' => $this->usuario_asignado,
            'creador_id'  => $this->usuario_creador
        ];
    }
}