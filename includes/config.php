<?php
// 🔐 Protección contra ejecución directa
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
  exit('Acceso directo no permitido.');
}

// 🕒 Zona horaria institucional
date_default_timezone_set('America/Caracas');

// 📦 Constantes institucionales
define('BASE_URL', '/matricula/');
define('APP_VERSION', '1.0.0');
define('APP_NAME', 'Sistema de Registro Académico');
define('APP_AUTOR', 'UED José Gil Fortoul - Desarrollado por Ingenieros en Formación UNETI');

// 📁 Rutas internas
define('PATH_LOGS', __DIR__ . '/../logs/');
define('PATH_PUBLIC', __DIR__ . '/../public/');
define('PATH_ASSETS', BASE_URL . 'public/assets/');
define('PATH_IMG', BASE_URL . 'public/assets/img/');

// 🔎 Validación de entorno
if (!is_dir(PATH_LOGS)) {
  mkdir(PATH_LOGS, 0755, true);
}