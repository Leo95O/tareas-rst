<?php
namespace App\Entities;

class CategoriaTarea {
    public $categoria_id;
    public $categoria_nombre;

    public function __construct($data = []) {
        if(!empty($data)) {
            $this->categoria_id = $data['categoria_id'] ?? null;
            $this->categoria_nombre = $data['categoria_nombre'] ?? null;
        }
    }
}