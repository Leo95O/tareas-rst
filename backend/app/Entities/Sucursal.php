<?php

namespace App\Entities;

use App\Constans\Estados; // Reutilizamos constantes globales (1=Activo, 2=Inactivo)

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
            // Mapeo básico de columnas
            $this->sucursal_id = isset($data['sucursal_id']) ? $data['sucursal_id'] : null;
            $this->sucursal_nombre = isset($data['sucursal_nombre']) ? $data['sucursal_nombre'] : null;
            $this->sucursal_direccion = isset($data['sucursal_direccion']) ? $data['sucursal_direccion'] : null;
            
            if (isset($data['sucursal_estado'])) {
                $this->sucursal_estado = $data['sucursal_estado'];
            }
        }
    }

    // Inyección de Dependencia para el Estado
    public function setEstado(EstadoSucursal $estado)
    {
        $this->estado = $estado;
        if ($estado->estado_id) {
            $this->sucursal_estado = $estado->estado_id;
        }
    }

    // Lógica de Negocio
    public function estaActiva()
    {
        if ($this->estado) {
            return $this->estado->estado_id === Estados::ACTIVO;
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
            
            // Objeto estado anidado para el frontend
            'estado'    => $this->estado ? [
                'id'     => $this->estado->estado_id,
                'nombre' => $this->estado->estado_nombre
            ] : null
        ];
    }
}