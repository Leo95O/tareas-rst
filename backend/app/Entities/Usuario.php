<?php

namespace App\Entities;

use App\Constants\EstadoUsuario;

class Usuario
{
    public $usuario_id;
    public $usuario_nombre; 
    public $usuario_correo;
    public $usuario_password;
    public $usuario_token;
    
    // 1. Relación con ROL (Foreign Key + Objeto)
    public $rol_id;
    /** @var Rol|null */
    public $rol;

    // 2. Relación con ESTADO (Foreign Key + Objeto)
    public $usuario_estado; // El ID en la tabla usuarios (int)
    /** @var EstadoUsuario|null */
    public $estado;         // El objeto completo hidratado

    public $fecha_creacion;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                // Evitamos sobrescribir las propiedades de objeto si vienen datos planos
                if (property_exists($this, $key) && $key !== 'rol' && $key !== 'estado') {
                    $this->$key = $value;
                }
            }
        }
    }

    // --- Setters para Inyección de Dependencias (Composición) ---

    public function setRol(Rol $rol)
    {
        $this->rol = $rol;
        if ($rol->rol_id) {
            $this->rol_id = $rol->rol_id; // Mantenemos sincronía
        }
    }

    public function setEstado(EstadoUsuario $estado)
    {
        $this->estado = $estado;
        if ($estado->estado_id) {
            $this->usuario_estado = $estado->estado_id; // Mantenemos sincronía
        }
    }

    // --- Lógica de Negocio (Domain Logic) ---

    public function estaActivo()
    {
        // Prioridad al objeto hidratado, fallback al ID crudo
        if ($this->estado) {
            return $this->estado->estado_id === EstadoUsuario::ACTIVO;
        }
        return (int)$this->usuario_estado === EstadoUsuario::ACTIVO;
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
            
            // Info de Rol
            'rol_id'          => $this->rol_id,
            'rol'             => $this->rol ? [
                'id' => $this->rol->rol_id,
                'nombre' => $this->rol->rol_nombre
            ] : null,

            // Info de Estado (Nueva estructura rica)
            'usuario_estado'  => $this->usuario_estado, // Mantenemos el ID plano por si acaso
            'estado'          => $this->estado ? [
                'id' => $this->estado->estado_id,
                'nombre' => $this->estado->estado_nombre,
                'descripcion' => $this->estado->estado_descripcion
            ] : null,

            'fecha_creacion'  => $this->fecha_creacion
        ];
    }
}