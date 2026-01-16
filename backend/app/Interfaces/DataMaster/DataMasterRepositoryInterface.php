<?php

namespace App\Interfaces\DataMaster;

interface DataMasterRepositoryInterface
{
    /**
     * Obtiene el catálogo de Roles de sistema (Admin, PM, User).
     * Tabla: roles
     * @return array Lista de objetos/arrays con 'id' y 'nombre'.
     */
    public function obtenerRoles();

    /**
     * Obtiene el catálogo de Estados para Usuarios (Activo, Inactivo).
     * Tabla: usuario_estados
     * @return array
     */
    public function obtenerEstadosUsuario();

    /**
     * Obtiene el catálogo de Estados para Sucursales.
     * Tabla: sucursal_estados
     * @return array
     */
    public function obtenerEstadosSucursal();

    /**
     * Obtiene el catálogo de Estados para Proyectos (Pendiente, En Progreso, etc.).
     * Tabla: proyecto_estados
     * @return array
     */
    public function obtenerEstadosProyecto();

    /**
     * Obtiene el catálogo de Estados para Tareas (Backlog, Doing, Done).
     * Tabla: tarea_estados
     * @return array
     */
    public function obtenerEstadosTarea();
    public function obtenerPrioridades();
}