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
            $data = [
                'roles'             => $this->repository->obtenerRoles(),
                'estados_usuario'   => $this->repository->obtenerEstadosUsuario(),
                'estados_sucursal'  => $this->repository->obtenerEstadosSucursal(),
                'estados_proyecto'  => $this->repository->obtenerEstadosProyecto(),
                'estados_tarea'     => $this->repository->obtenerEstadosTarea(),
                'prioridades'       => $this->repository->obtenerPrioridades() // <--- AGREGAR ESTO
            ];

            ApiResponse::exito("Catálogos cargados", $data);
        } catch (\Exception $e) {
            ApiResponse::error("Error al cargar datos maestros: " . $e->getMessage());
        }
    }
}