<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Uso vlucas/phpdotenv para cargar variables de entorno desde un archivo .env
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    // Forzar que las variables también estén disponibles en getenv()
    // phpdotenv v4 solo carga en $_ENV y $_SERVER por defecto
    foreach ($_ENV as $key => $value) {
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }
}

use App\Config\Database;

// Cargo constantes de configuración global
define('APP_NAME', 'Task Manager');
define('APP_ROOT', dirname(__DIR__));
define('JSON_RESPONSE_WRAPPER', true);

// Inicio la conexión a la base de datos con patron Singleton
try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'tipo' => 3, // Error
        'mensajes' => ['Error crítico de conexión a Base de Datos'],
        'data' => []
    ]);
    exit;
}
