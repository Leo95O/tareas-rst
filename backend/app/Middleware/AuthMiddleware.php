<?php

namespace App\Middleware;

use Slim\Middleware;
use App\Interfaces\Usuario\UsuarioRepositoryInterface;
use App\Utils\ApiResponse;
use App\Utils\Auth; // Asegúrate de tener este Use

class AuthMiddleware extends Middleware
{
    private $usuarioRepo;

    // Rutas públicas que no requieren token
    private $rutasPublicas = [
        '/usuarios/login',
        '/usuarios/registro',
        '/datamaster/categorias', // Si estas son públicas
        '/datamaster/prioridades',
        '/datamaster/estados',
        '/datamaster/sucursales',
        '/datamaster/estados-proyecto'
    ];

    public function __construct(UsuarioRepositoryInterface $repo)
    {
        $this->usuarioRepo = $repo;
    }

    public function call()
    {
        $app = $this->app;
        $req = $app->request;
        $res = $app->response;
        
        // 1. CORRECCIÓN CRÍTICA: Permitir siempre las peticiones OPTIONS (CORS Preflight)
        if ($req->isOptions()) {
            $this->next->call();
            return;
        }

        $rutaActual = $req->getResourceUri();

        // 2. Si la ruta es pública, dejamos pasar
        // (Mejoramos la lógica para que coincida aunque haya slash final)
        foreach ($this->rutasPublicas as $ruta) {
            if (strpos($rutaActual, $ruta) === 0) { // Coincidencia parcial o exacta
                $this->next->call();
                return;
            }
        }

        // 3. Validar Token para rutas privadas
        $authHeader = $req->headers->get('Authorization');

        if (!$authHeader) {
            $this->denegarAcceso("Token de autorización no proporcionado.");
            return;
        }

        list($token) = sscanf($authHeader, 'Bearer %s');

        if (!$token) {
            $this->denegarAcceso("Formato de token inválido.");
            return;
        }

        try {
            // Decodificar Token
            $decoded = Auth::verificarToken($token);

            // Verificar si el token en BD sigue siendo el mismo (Single Session)
            // (Opcional, depende de tu lógica estricta, pero recomendado)
            $usuario = $this->usuarioRepo->obtenerPorId($decoded->sub);
            
            if (!$usuario || $usuario->usuario_token !== $token) {
                 $this->denegarAcceso("Sesión inválida o expirada.");
                 return;
            }

            // Inyectar usuario en la app para los controladores
            $app->usuario = $usuario;

            // Continuar
            $this->next->call();

        } catch (\Exception $e) {
            $this->denegarAcceso("Token inválido o expirado: " . $e->getMessage());
        }
    }

    private function denegarAcceso($mensaje)
    {
        $app = $this->app;
        $app->response->status(401);
        $app->response->headers->set('Content-Type', 'application/json');
        
        // Importante: Aunque sea error, intentamos poner cabeceras CORS por si acaso el CorsMiddleware no corrió
        $app->response->headers->set('Access-Control-Allow-Origin', '*'); 
        
        echo json_encode([
            'success' => false,
            'message' => $mensaje
        ]);
    }
}