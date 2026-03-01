<?php
/**
 * init.php - Ubicado en /matricula/includes/init.php
 */

// 1. Definir la raíz física del proyecto
// Como estamos dentro de /includes, necesitamos subir UN nivel para llegar a /matricula
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

// 2. Definir rutas internas
define('INC_PATH', ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR);
define('MOD_PATH', ROOT_PATH . 'modulos' . DIRECTORY_SEPARATOR);

// 3. Cargar la base de datos
$dbFile = INC_PATH . 'db.php'; 
if (file_exists($dbFile)) {
    require_once $dbFile;
} else {
    die("Error crítico: No se encuentra db.php en: " . $dbFile);
}

// 4. Rutas para el navegador
define('BASE_URL', '/matricula/');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}