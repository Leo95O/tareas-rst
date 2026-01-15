<?php

namespace App\Controllers;

use App\Interfaces\DataMaster\DataMasterRepositoryInterface;
use App\Utils\ApiResponse;

class DataMasterController
{
    private $repository;

    public function __construct(DataMasterRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function obtenerCatalogos()
    {
        try {
            // Empaquetamos TODOS los catálogos necesarios para la APP
            $data = [
                'roles'             => $this->repository->obtenerRoles(),
                'estados_usuario'   => $this->repository->obtenerEstadosUsuario(),  // ¡Nuevo!
                'estados_sucursal'  => $this->repository->obtenerEstadosSucursal(), // ¡Nuevo!
                'estados_proyecto'  => $this->repository->obtenerEstadosProyecto(),
                'estados_tarea'     => $this->repository->obtenerEstadosTarea()
            ];

            ApiResponse::exito("Catálogos del sistema cargados correctamente.", $data);
        } catch (\Exception $e) {
            ApiResponse::error("Error al cargar datos maestros: " . $e->getMessage());
        }
    }
}