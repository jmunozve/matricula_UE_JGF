<?php
/**
 * F:\xampp\htdocs\matricula\includes\db.php
 */

// Definición de constantes si no han sido definidas previamente
if (!defined('DB_SERVER')) define('DB_SERVER', 'localhost');
if (!defined('DB_USER'))   define('DB_USER', 'root');
if (!defined('DB_PASS'))   define('DB_PASS', '');
if (!defined('DB_NAME'))   define('DB_NAME', 'db_institucion');

class Conexion {
    private static $pdo = null;

    public static function abrir() {
        // Usamos el patrón Singleton para no abrir múltiples conexiones en un mismo proceso
        if (self::$pdo === null) {
            try {
                $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                
                // Opciones avanzadas para estabilidad y seguridad
                $opciones = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
                
            } catch (PDOException $e) {
                // Importante: Si es una petición AJAX, el die debe ser controlado
                // para no romper el formato JSON de respuesta.
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Fallo de conexión a BD: ' . $e->getMessage()
                ]);
                exit;
            }
        }
        return self::$pdo;
    }
}