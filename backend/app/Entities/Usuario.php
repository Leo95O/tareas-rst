<?php

namespace App\Entities;

use App\Constants\Estados; 
use App\Constants\Roles;   

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

    /** @var EstadoUsuario|null */
    public $estado; 

    public $fecha_creacion;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key) && $key !== 'rol' && $key !== 'estado') {
                    // Casteo de IDs a entero para evitar fallos de comparación estricta (===)
                    if (in_array($key, ['usuario_id', 'rol_id', 'usuario_estado'])) {
                        $this->$key = (int)$value;
                    } else {
                        $this->$key = $value;
                    }
                }
            }
        }
    }

    public function setRol(Rol $rol)
    {
        $this->rol = $rol;
        if ($rol->rol_id) {
            $this->rol_id = (int)$rol->rol_id; 
        }
    }

    public function setEstado(EstadoUsuario $estado)
    {
        $this->estado = $estado;
        if ($estado->estado_id) {
            $this->usuario_estado = (int)$estado->estado_id; 
        }
    }

    // --- Lógica de Negocio ---

    public function estaActivo()
    {
        // Si el objeto estado está cargado, priorizamos su validación
        if ($this->estado) {
            return (int)$this->estado->estado_id === Estados::ACTIVO;
        }
        // Si no, usamos el ID plano de la propiedad
        return (int)$this->usuario_estado === Estados::ACTIVO;
    }

    public function esAdmin()
    {
        // Comparación flexible para roles
        $id = $this->rol ? $this->rol->rol_id : $this->rol_id;
        return (int)$id === Roles::ADMIN;
    }

    public function toArray()
    {
        return [
            'usuario_id'      => $this->usuario_id,
            'usuario_nombre'  => $this->usuario_nombre,
            'usuario_correo'  => $this->usuario_correo,
            'rol_id'          => $this->rol_id,
            'rol'             => $this->rol ? [
                'id' => (int)$this->rol->rol_id,
                'nombre' => $this->rol->rol_nombre
            ] : null,
            'usuario_estado'  => $this->usuario_estado,
            'estado'          => $this->estado ? [
                'id' => (int)$this->estado->estado_id,
                'nombre' => $this->estado->estado_nombre,
                'descripcion' => $this->estado->estado_descripcion
            ] : null,
            'fecha_creacion'  => $this->fecha_creacion
        ];
    }
}