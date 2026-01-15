<?php

namespace App\Entities;

class Sucursal
{
    public $sucursal_id;
    public $sucursal_nombre;
    public $sucursal_direccion;
    public $sucursal_estado;

    public function __construct(array $data = [])
    {
        $this->sucursal_id = isset($data['sucursal_id']) ? $data['sucursal_id'] : null;
        $this->sucursal_nombre = isset($data['sucursal_nombre']) ? $data['sucursal_nombre'] : null;
        $this->sucursal_direccion = isset($data['sucursal_direccion']) ? $data['sucursal_direccion'] : null;
        $this->sucursal_estado = isset($data['sucursal_estado']) ? $data['sucursal_estado'] : null;
    }

    public function toArray()
    {
        return [
            'sucursal_id' => $this->sucursal_id,
            'sucursal_nombre' => $this->sucursal_nombre,
            'sucursal_direccion' => $this->sucursal_direccion,
            'sucursal_estado' => $this->sucursal_estado
        ];
    }
}