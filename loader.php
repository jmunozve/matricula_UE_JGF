<?php
// 📦 Definiciones institucionales
define('BASE_PATH', __DIR__);
define('PATH_LOGS', BASE_PATH . '/logs/');
define('INCLUDES_PATH', BASE_PATH . '/includes/');
define('MODULOS_PATH', BASE_PATH . '/modulos/');

// 🔧 Función para rutas blindadas
function ruta_absoluta($archivo) {
  $mapa = [
    'config' => INCLUDES_PATH . 'config.php',
    'db'     => INCLUDES_PATH . 'db.php',
    'logs'   => PATH_LOGS,
    'modulo' => MODULOS_PATH
  ];
  return $mapa[$archivo] ?? null;
}

// 🛡️ Validación mínima
$configPath = ruta_absoluta('config');
$dbPath     = ruta_absoluta('db');

if (!file_exists($configPath)) {
  error_log(date('[Y-m-d H:i:s]') . " ERROR: config.php no encontrado\n", 3, ruta_absoluta('logs') . 'errores.log');
  die("Error crítico: config.php no disponible.");
}
if (!file_exists($dbPath)) {
  error_log(date('[Y-m-d H:i:s]') . " ERROR: db.php no encontrado\n", 3, ruta_absoluta('logs') . 'errores.log');
  die("Error crítico: db.php no disponible.");
}

// 🔗 Inclusión segura
require_once $configPath;
require_once $dbPath;

// 🧠 Conexión PDO
if (!class_exists('Conexion')) {
  error_log(date('[Y-m-d H:i:s]') . " ERROR: Clase Conexion no definida\n", 3, ruta_absoluta('logs') . 'errores.log');
  die("Error crítico: clase Conexion no disponible.");
}
$pdo = Conexion::abrir();
