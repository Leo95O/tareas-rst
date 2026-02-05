<?php

namespace App\Interfaces\Context;

interface ContextRepositoryInterface
{
    /**
     * Verifica si existe una relación válida entre un usuario y una sucursal.
     * @param int $usuarioId
     * @param int $sucursalId
     * @return bool
     */
    public function verificarAcceso($usuarioId, $sucursalId);

    /**
     * Obtiene los datos mínimos necesarios de la sucursal (id y nombre)
     * para inyectarlos en el token.
     * @param int $sucursalId
     * @return array|false
     */
    public function obtenerSucursalData($sucursalId);
}