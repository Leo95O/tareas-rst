<?php

namespace App\Interfaces\DataMaster;

interface DataMasterRepositoryInterface
{
  
    public function obtenerRoles();
    public function obtenerEstadosUsuario();
    public function obtenerEstadosSucursal();
    public function obtenerEstadosProyecto();
    public function obtenerEstadosTarea();
    public function obtenerPrioridades();
}