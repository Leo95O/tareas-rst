<?php

namespace App\Entities;

use App\Constants\Estados; // Para evitar el número mágico 1 (Activo)
use App\Constants\Roles;   // Para evitar el número mágico 1 (Admin)

class Usuario
{
    public $usuario_id;
    public $usuario_nombre; 
    public $usuario_correo;
    public $usuario_password;
    public $usuario_token;
    
    public $rol_id;
    /** @var Rol|null */
    public $rol; // PHP detecta automáticamente App\Entities\Rol

    public $usuario_estado;

    /** @var EstadoUsuario|null */
    public $estado; // PHP detecta automáticamente App\Entities\EstadoUsuario

    public $fecha_creacion;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                // Protección contra inyección de datos planos en propiedades de objeto
                if (property_exists($this, $key) && $key !== 'rol' && $key !== 'estado') {
                    $this->$key = $value;
                }
            }
        }
    }

    // --- Setters con Inyección de Dependencias ---

    public function setRol(Rol $rol)
    {
        $this->rol = $rol;
        // Mantenemos la integridad referencial del ID
        if ($rol->rol_id) {
            $this->rol_id = $rol->rol_id; 
        }
    }

    /**
     * Aquí estaba el error: Debemos esperar la Entidad (EstadoUsuario), no la Constante (Estados).
     * La Entidad tiene datos ($estado_id), la Constante solo tiene valores fijos.
     */
    public function setEstado(EstadoUsuario $estado)
    {
        $this->estado = $estado;
        // Sincronizamos el ID plano con el objeto
        if ($estado->estado_id) {
            $this->usuario_estado = $estado->estado_id; 
        }
    }

    // --- Lógica de Negocio (Domain Logic) ---

    public function estaActivo()
    {
        // Usamos la constante (Estados::ACTIVO) para evitar números mágicos
        if ($this->estado) {
            // Comparamos el dato de la Entidad contra la Constante
            return $this->estado->estado_id === Estados::ACTIVO;
        }
        return (int)$this->usuario_estado === Estados::ACTIVO;
    }

    public function esAdmin()
    {
        // Usamos la constante (Roles::ADMIN) para evitar el número mágico "1"
        return $this->rol_id == Roles::ADMIN || ($this->rol && $this->rol->rol_id == Roles::ADMIN);
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
            
            // Mapeo limpio para el Frontend
            'estado'          => $this->estado ? [
                'id' => $this->estado->estado_id,
                'nombre' => $this->estado->estado_nombre,
                'descripcion' => $this->estado->estado_descripcion
            ] : null,

            'fecha_creacion'  => $this->fecha_creacion
        ];
    }
}