<?php
namespace App\Entities;

class EstadoTarea {
    public $estado_id;
    public $estado_nombre;
    public $estado_orden;

    public function __construct($data = []) {
        if(!empty($data)) {
            $this->estado_id = $data['estado_id'] ?? null;
            $this->estado_nombre = $data['estado_nombre'] ?? null;
            $this->estado_orden = $data['estado_orden'] ?? null;
        }
    }
}