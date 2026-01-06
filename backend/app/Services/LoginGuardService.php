<?php

namespace App\Services;

use App\Repositories\LoginGuardRepository;
use Exception;
use DateTime;

class LoginGuardService
{
    private $repository;

    public function __construct()
    {
        // Inicializa el repositorio de control de logins
        $this->repository = new LoginGuardRepository();
    }

    // Verifica si el usuario puede intentar iniciar sesión
    public function verificarSiPuedeEntrar($correo)
    {
        $estado = $this->repository->obtenerEstado($correo);

        if ($estado) {
            // Valida bloqueo permanente
            if ($estado['nivel_bloqueo'] >= 3) {
                throw new Exception("Tu cuenta ha sido bloqueada permanentemente. Contacta al soporte.");
            }

            // Valida bloqueo temporal
            if (!empty($estado['bloqueado_hasta'])) {
                $ahora = new DateTime();
                $hasta = new DateTime($estado['bloqueado_hasta']);

                if ($ahora < $hasta) {
                    $diff = $hasta->diff($ahora);
                    throw new Exception("Cuenta bloqueada temporalmente. Espera {$diff->i} min y {$diff->s} seg.");
                }
            }
        }

        // Retorna el estado si no hay bloqueo
        return $estado;
    }

    // Procesa un intento de login fallido
    public function procesarIntentoFallido($correo, $estadoActual)
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

        // Resetea intentos si pasaron más de 2 minutos
        if ($ultimoIntento) {
            $diffMinutos = ($ahora->getTimestamp() - $ultimoIntento->getTimestamp()) / 60;
            if ($diffMinutos > 2) {
                $intentos = 0;
            }
        }

        $intentos++;
        $bloqueadoHasta = null;

        // Incrementa nivel de bloqueo cada 3 intentos
        if ($intentos >= 3) {
            $nivel++;
            $intentos = 0;

            if ($nivel === 1) {
                $ahora->modify('+5 minutes');
                $bloqueadoHasta = $ahora->format('Y-m-d H:i:s');
            } elseif ($nivel === 2) {
                $ahora->modify('+10 minutes');
                $bloqueadoHasta = $ahora->format('Y-m-d H:i:s');
            } elseif ($nivel >= 3) {
                $bloqueadoHasta = null;
            }
        }

        $this->repository->registrarFallo($correo, $intentos, $nivel, $bloqueadoHasta);
    }

    // Limpia el historial de fallos del usuario
    public function limpiarHistorial($correo)
    {
        $this->repository->limpiar($correo);
    }
}
