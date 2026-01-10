<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    // La única instancia estática de la clase (Singleton)
    private static $instance = null;
    
    // La conexión PDO real
    private $conn;

    // Configuración
    private $host;
    private $db_name;
    private $username;
    private $password;

    // Constructor privado: Nadie fuera de esta clase puede hacer "new Database()"
    private function __construct()
    {
        // 1. Cargar credenciales de las variables de entorno (o usar valores por defecto)
        $this->host     = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name  = $_ENV['DB_NAME'] ?? 'admin_tareas';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';

        // 2. Definir opciones de PDO (El array optimizado que discutimos)
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones si hay error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arrays asociativos
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa sentencias preparadas reales
        ];

        // 3. Construir el DSN (Data Source Name)
        // Nota: charset=utf8mb4 es vital para soportar emojis y caracteres especiales completos
        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";

        try {
            // 4. Crear la conexión pasando las opciones directamente al nacer
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

        } catch (PDOException $exception) {
            // Buena práctica: Escribir el error en el log interno del servidor (error.log)
            // pero NO mostrar detalles sensibles al usuario en pantalla, por eso relanzamos.
            error_log("Error de conexión a Base de Datos: " . $exception->getMessage());
            
            // Relanzamos la excepción para que la capture el try-catch global de config.php
            throw $exception;
        }
    }

    // Método estático para obtener la instancia única
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Método para obtener el objeto PDO y hacer consultas
    public function getConnection()
    {
        return $this->conn;
    }

    private function __clone() {}
    public function __wakeup() {}
}