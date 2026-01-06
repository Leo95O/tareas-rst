<?php

namespace App\Entities;

class Proyecto
{
    public $proyecto_id;
    public $proyecto_nombre;
    public $proyecto_descripcion;
    public $sucursal_id;
    public $estado_id;
    public $usuario_creador;
    public $fecha_inicio;
    public $fecha_fin;
    public $fecha_creacion;
    public $fecha_eliminacion;

    // Campos de JOIN
    public $nombre_creador;
    public $sucursal_nombre;
    public $estado_nombre;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Verifica si el proyecto está eliminado (Soft Delete)
     */
    public function estaEliminado()
    {
        return !empty($this->fecha_eliminacion);
    }

    public function toArray()
    {
        return [
            'id' => $this->proyecto_id,
            'nombre' => $this->proyecto_nombre,
            'descripcion' => $this->proyecto_descripcion,
            'sucursal_id' => $this->sucursal_id,
            'sucursal_nombre' => $this->sucursal_nombre,
            'estado_id' => $this->estado_id,
            'estado_nombre' => $this->estado_nombre,
            'creador_id' => $this->usuario_creador,
            'creador_nombre' => $this->nombre_creador,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'fecha_creacion' => $this->fecha_creacion,
            'eliminado' => $this->estaEliminado()
        ];
    }
}