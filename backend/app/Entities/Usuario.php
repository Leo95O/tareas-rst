<?php

namespace App\Entities;

use App\Constans\Estados;

class Usuario
{
    public $usuario_id;
    public $usuario_nombre; 
    public $usuario_correo;
    public $usuario_password;
    public $usuario_token;
    
    public $rol_id;
    /** @var Rol|null */
    public $rol;

    public $usuario_estado;

    /** @var Estados|null */
    public $estado; // PHP busca App\Entities\EstadoUsuario automáticamente

    public $fecha_creacion;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key) && $key !== 'rol' && $key !== 'estado') {
                    $this->$key = $value;
                }
            }
        }
    }

    // --- Setters ---

    public function setRol(Rol $rol)
    {
        $this->rol = $rol;
        if ($rol->rol_id) {
            $this->rol_id = $rol->rol_id; 
        }
    }

    public function setEstado(Estados $estado)
    {
        $this->estado = $estado;
        if ($estado->estado_id) {
            $this->usuario_estado = $estado->estado_id; 
        }
    }

    // --- Lógica de Negocio ---

    public function estaActivo()
    {
        // Se lee natural: "¿El estado ID es igual a Estados::ACTIVO?"
        if ($this->estado) {
            return $this->estado->estado_id === Estados::ACTIVO;
        }
        return (int)$this->usuario_estado === Estados::ACTIVO;
    }
    public function esAdmin()
    {
        return $this->rol_id == 1 || ($this->rol && $this->rol->rol_id == 1);
    }

    public function toArray()
    {
        return [
            'usuario_id'      => $this->usuario_id,
            'usuario_nombre'  => $this->usuario_nombre,
            'usuario_correo'  => $this->usuario_correo,
            
            'rol_id'          => $this->rol_id,
            'rol'             => $this->rol ? [
                'id' => $this->rol->rol_id,
                'nombre' => $this->rol->rol_nombre
            ] : null,

            'usuario_estado'  => $this->usuario_estado,
            'estado'          => $this->estado ? [
                'id' => $this->estado->estado_id,
                'nombre' => $this->estado->estado_nombre,
                'descripcion' => $this->estado->estado_descripcion
            ] : null,

            'fecha_creacion'  => $this->fecha_creacion
        ];
    }
}