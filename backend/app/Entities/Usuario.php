<?php

namespace App\Entities;

class Usuario
{
    public $usuario_id;
    public $usuario_nombre; 
    public $usuario_correo;
    public $usuario_password;
    public $usuario_token;
    public $rol_id;
    public $usuario_estado;
    public $fecha_creacion;


    public function __construct($data = [])
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function estaActivo()
    {
        return $this->usuario_estado == 1;
    }

    public function esAdmin()
    {
        return $this->rol_id == 1;
    }

    public function toArray()
    {
        return [
            'usuario_id' => $this->usuario_id,
            'usuario_nombre' => $this->usuario_nombre,
            'usuario_correo' => $this->usuario_correo,
            'rol_id' => $this->rol_id,
            'usuario_estado' => $this->usuario_estado,
            'fecha_creacion' => $this->fecha_creacion
        ];
    }
}
