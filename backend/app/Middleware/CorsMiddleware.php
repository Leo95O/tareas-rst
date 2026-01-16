<?php

namespace App\Middleware;

use \Slim\Middleware;
class CorsMiddleware
{
    public function call()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        if ($this->app->request->isOptions()) {
            http_response_code(200);
            exit;
        }

        $this->next->call();
    }
}