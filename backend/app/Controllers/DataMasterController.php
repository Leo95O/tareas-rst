<?php

namespace App\Controllers;

use App\Repositories\DataMasterRepository;
use App\Utils\ApiResponse;

class DataMasterController
{
    private $repository;

    public function __construct()
    {
        $this->repository = new DataMasterRepository();
    }

    public function getCategorias()
    {
        try {
            $data = $this->repository->listarCategorias();
            ApiResponse::exito("Categorías recuperadas.", $data);
        } catch (\Exception $e) {
            ApiResponse::error("Error: " . $e->getMessage());
        }
    }

    public function getPrioridades()
    {
        try {
            $data = $this->repository->listarPrioridades();
            ApiResponse::exito("Prioridades recuperadas.", $data);
        } catch (\Exception $e) {
            ApiResponse::error("Error: " . $e->getMessage());
        }
    }

    public function getEstados()
    {
        try {
            $data = $this->repository->listarEstados();
            ApiResponse::exito("Estados recuperados.", $data);
        } catch (\Exception $e) {
            ApiResponse::error("Error: " . $e->getMessage());
        }
    }

    public function getSucursales()
    {
        try {
            $data = $this->repository->listarSucursales();
            ApiResponse::exito("Sucursales recuperadas.", $data);
        } catch (\Exception $e) {
            ApiResponse::error("Error: " . $e->getMessage());
        }
    }

    public function getEstadosProyecto()
    {
        try {
            $data = $this->repository->listarEstadosProyecto();
            ApiResponse::exito("Estados de proyecto recuperados.", $data);
        } catch (\Exception $e) {
            ApiResponse::error("Error: " . $e->getMessage());
        }
    }
}