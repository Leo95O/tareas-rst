<?php

namespace App\Interfaces\Proyecto;

use App\Entities\Proyecto;

interface ProyectoRepositoryInterface
{
    public function listar($usuarioId, $rolId);
    public function listarPorUsuario($usuarioId);
    public function obtenerPorId($id);
    public function crear(Proyecto $proyecto);
    public function actualizar(Proyecto $proyecto);
    
    // El segundo parámetro es opcional en la implementación (= null), 
    // pero en la interfaz basta con definir que recibe dos argumentos o declararlo igual.
    public function eliminar($id, $usuarioId = null);
    
    public function existeNombre($nombre, $excluirId = null);
}