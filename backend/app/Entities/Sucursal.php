<?php

namespace App\Entities;

use App\Constants\Estados;

class Sucursal
{
    public $sucursal_id;
    public $sucursal_nombre;
    public $sucursal_direccion;
    
    public $sucursal_estado; // ID plano (FK)
    
    /** @var EstadoSucursal|null */
    public $estado; // Objeto hidratado

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key) && $key !== 'estado') {
                    // Aseguramos que los IDs y estados sean siempre enteros
                    if (in_array($key, ['sucursal_id', 'sucursal_estado'])) {
                        $this->$key = $value !== null ? (int)$value : null;
                    } else {
                        $this->$key = $value;
                    }
                }
            }
        }
    }

    public function setEstado(EstadoSucursal $estado)
    {
        $this->estado = $estado;
        if ($estado->estado_id) {
            $this->sucursal_estado = (int)$estado->estado_id;
        }
    }

    public function estaActiva()
    {
        if ($this->estado) {
            return (int)$this->estado->estado_id === Estados::ACTIVO;
        }
        return (int)$this->sucursal_estado === Estados::ACTIVO;
    }

    public function toArray()
    {
        return [
            'id'        => $this->sucursal_id,
            'nombre'    => $this->sucursal_nombre,
            'direccion' => $this->sucursal_direccion,
            'estado_id' => $this->sucursal_estado,
            'estado'    => $this->estado ? [
                'id'     => (int)$this->estado->estado_id,
                'nombre' => $this->estado->estado_nombre
            ] : null
        ];
    }
}