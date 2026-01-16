<?php

namespace App\Constans;

class EstadosProyecto {
    // Basado en tu tabla 'proyecto_estados' del dump SQL
    // Ajusta los IDs si en tu BD son diferentes, pero asumo el estándar:
    const PENDIENTE = 1;
    const EN_PROGRESO = 2;
    const FINALIZADO = 3;
    const CANCELADO = 4;
}