<?php

namespace App\Middleware;

use \Slim\Middleware;

class CorsMiddleware extends Middleware
{
    public function call()
    {
        // 1. Usamos el objeto Response de Slim en lugar de 'header()' nativo.
        // Esto permite que Slim sepa qué headers se enviarán.
        $this->app->response->headers->set('Access-Control-Allow-Origin', '*');
        $this->app->response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $this->app->response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');

        // 2. Manejo de Preflight (OPTIONS)
        if ($this->app->request->isOptions()) {
            // Simplemente establecemos el estado 200 y retornamos.
            // Al NO llamar a $this->next->call(), la cadena se detiene aquí suavemente.
            // No hace falta 'exit' ni 'http_response_code'.
            $this->app->response->status(200);
            return;
        }

        // 3. Si no es OPTIONS, dejamos pasar la petición al siguiente nivel
        $this->next->call();
    }
}