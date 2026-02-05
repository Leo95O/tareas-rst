<?php
namespace App\Controllers;

use App\Interfaces\Context\ContextServiceInterface;
use App\Utils\ApiResponse;
use App\Exceptions\ValidationException;
use App\Validators\ContextValidator; // Asumimos que creas uno simple

class ContextController
{
    private $service;

    public function __construct(ContextServiceInterface $service) {
        $this->service = $service;
    }

    public function switchBranch($datos, $usuarioId)
    {
        try {
            // Validación simple (puedes moverla a ContextValidator)
            if (empty($datos['sucursal_id'])) {
                throw new ValidationException("Debes enviar el ID de la sucursal.");
            }

            $resultado = $this->service->cambiarContexto($usuarioId, $datos['sucursal_id']);

            echo ApiResponse::exito("Contexto cambiado correctamente.", $resultado);

        } catch (ValidationException $e) {
            echo ApiResponse::alerta($e->getMessage());
        } catch (\Exception $e) {
            error_log("Context Error: " . $e->getMessage());
            echo ApiResponse::error("Error interno al cambiar de contexto.");
        }
    }
}