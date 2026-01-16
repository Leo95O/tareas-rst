<?php

namespace App\Services;

use App\Interfaces\LoginGuard\LoginGuardServiceInterface;
use App\Interfaces\LoginGuard\LoginGuardRepositoryInterface;
use App\Exceptions\ValidationException;
use DateTime;

class LoginGuardService implements LoginGuardServiceInterface
{
    private $repository;

    public function __construct(LoginGuardRepositoryInterface $repo)
    {
        $this->repository = $repo;
    }

    public function verificarSiPuedeEntrar($usuarioHash)
    {
        $estado = $this->repository->obtenerEstado($usuarioHash);

        if ($estado) {
            // 1. Bloqueo Permanente (Nivel 3 o superior)
            if ((int)$estado['nivel_bloqueo'] >= 3) {
                throw new ValidationException("Esta cuenta ha sido bloqueada permanentemente por seguridad. Contacte al administrador.");
            }

            // 2. Bloqueo Temporal activo
            if (!empty($estado['bloqueado_hasta'])) {
                $ahora = new DateTime();
                $hasta = new DateTime($estado['bloqueado_hasta']);

                if ($ahora < $hasta) {
                    $diff = $hasta->diff($ahora);
                    $tiempo = "";
                    if ($diff->i > 0) $tiempo .= "{$diff->i} min ";
                    $tiempo .= "{$diff->s} seg";
                    
                    throw new ValidationException("Demasiados intentos fallidos. Por seguridad, intente nuevamente en: {$tiempo}.");
                }
            }
        }

        return $estado;
    }

    public function procesarIntentoFallido($usuarioHash, $estadoActual)
    {
        $ahora = new DateTime();
        $intentos = 0;
        $nivel = 0;
        $ultimoIntento = null;

        if ($estadoActual) {
            $intentos = (int) $estadoActual['intentos_fallidos'];
            $nivel = (int) $estadoActual['nivel_bloqueo'];
            $ultimoIntento = $estadoActual['ultimo_intento'] ? new DateTime($estadoActual['ultimo_intento']) : null;
        }

        // Si han pasado más de 15 minutos, reseteamos intentos pero mantenemos el nivel de sospecha
        if ($ultimoIntento) {
            $diffMinutos = ($ahora->getTimestamp() - $ultimoIntento->getTimestamp()) / 60;
            if ($diffMinutos > 15) {
                $intentos = 0;
            }
        }

        $intentos++;
        $bloqueadoHasta = null;

        // Regla: 3 intentos fallidos activan/escalan un bloqueo
        if ($intentos >= 3) {
            $nivel++;
            $intentos = 0; // Reiniciamos contador de intentos para el siguiente nivel

            if ($nivel === 1) {
                $ahora->modify('+5 minutes');
                $bloqueadoHasta = $ahora->format('Y-m-d H:i:s');
            } elseif ($nivel === 2) {
                $ahora->modify('+15 minutes');
                $bloqueadoHasta = $ahora->format('Y-m-d H:i:s');
            } elseif ($nivel >= 3) {
                // El nivel 3 ya se considera permanente en verificarSiPuedeEntrar
                $bloqueadoHasta = '2099-12-31 23:59:59'; 
            }
        }

        // Llamada sincronizada con LoginGuardRepository
        $this->repository->registrarIntento($usuarioHash, $intentos, $nivel, $bloqueadoHasta);
    }

    public function limpiarHistorial($usuarioHash)
    {
        $this->repository->limpiarCuentas($usuarioHash);
    }
}