<?php
namespace App\Entities;

class UsuarioSucursal
{
    public $usuario_sucursal_id;
    public $usuario_id;
    public $sucursal_id;
    public $fecha_asignacion;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->usuario_sucursal_id = isset($data['usuario_sucursal_id']) ? (int)$data['usuario_sucursal_id'] : null;
            $this->usuario_id          = isset($data['usuario_id']) ? (int)$data['usuario_id'] : null;
            $this->sucursal_id         = isset($data['sucursal_id']) ? (int)$data['sucursal_id'] : null;
            $this->fecha_asignacion    = $data['fecha_asignacion'] ?? null;
        }
    }
}