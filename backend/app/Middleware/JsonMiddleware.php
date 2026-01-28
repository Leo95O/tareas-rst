<?php

namespace App\Middleware;

use \Slim\Middleware;

class JsonMiddleware extends Middleware
{
    public function call()
    {
        // 1. Configuración PREVIA (Antes de que se procese la ruta)
        // Definimos que esta API siempre responde JSON, pase lo que pase.
        $this->app->response->headers->set('Content-Type', 'application/json; charset=utf-8');

        // 2. Pasar la pelota al siguiente middleware o a la aplicación
        $this->next->call();
    }
}