<?php

namespace App\Controllers;

use App\Interfaces\DataMaster\DataMasterRepositoryInterface;
use App\Utils\ApiResponse;
use Exception;

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
                'prioridades'       => $this->repository->obtenerPrioridades()
            ];

            echo ApiResponse::exito("Catálogos cargados correctamente.", $data);
        } catch (Exception $e) {
            // Registramos el error técnico para depuración interna
            error_log("Error en DataMasterController::obtenerCatalogos: " . $e->getMessage());
            
            // Respuesta genérica al cliente por seguridad
            echo ApiResponse::error("Ocurrió un error interno al intentar cargar los catálogos del sistema.");
        }
    }
}