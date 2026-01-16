<?php

namespace App\Entities;

class EstadoProyecto
{
    public $estado_id;
    public $estado_nombre;
    public $estado_orden; // Tu tabla tiene esta columna extra

    public function __construct($data = [])
    {
        if (!empty($data)) {
            $this->estado_id = isset($data['estado_id']) ? (int)$data['estado_id'] : null;
            $this->estado_nombre = isset($data['estado_nombre']) ? $data['estado_nombre'] : null;
            $this->estado_orden = isset($data['estado_orden']) ? (int)$data['estado_orden'] : null;
        }
    }
}