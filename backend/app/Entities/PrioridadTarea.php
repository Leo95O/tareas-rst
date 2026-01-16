<?php
namespace App\Entities;

class PrioridadTarea {
    public $prioridad_id;
    public $prioridad_nombre;
    public $prioridad_valor;

    public function __construct($data = []) {
        if(!empty($data)) {
            $this->prioridad_id = $data['prioridad_id'] ?? null;
            $this->prioridad_nombre = $data['prioridad_nombre'] ?? null;
            $this->prioridad_valor = $data['prioridad_valor'] ?? null;
        }
    }
}