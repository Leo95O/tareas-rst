<?php
namespace App\Interfaces;

interface DataMasterRepositoryInterface
{
    public function listarCategorias();
    public function listarPrioridades();
    public function listarEstados();
    public function listarSucursales();
    public function listarEstadosProyecto();
}