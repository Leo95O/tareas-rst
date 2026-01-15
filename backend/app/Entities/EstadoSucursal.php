<?php

namespace App\Entities;

class EstadoSucursal
{
    public $estado_id;
    public $estado_nombre;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            $this->estado_id = isset($data['estado_id']) ? (int)$data['estado_id'] : null;
            $this->estado_nombre = isset($data['estado_nombre']) ? $data['estado_nombre'] : null;
        }
    }
}