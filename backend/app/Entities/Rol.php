<?php

namespace App\Entities;

class Rol
{
    public $rol_id;
    public $rol_nombre;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            $this->rol_id = isset($data['rol_id']) ? $data['rol_id'] : null;
            $this->rol_nombre = isset($data['rol_nombre']) ? $data['rol_nombre'] : null;
        }
    }
}