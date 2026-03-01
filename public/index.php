<?php
// Activar logging técnico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ruta al config
$configPath = dirname(__DIR__) . '/includes/config.php';

if (!file_exists($configPath)) {
    error_log("ERROR: No se encontró config.php en $configPath");
    die('Error crítico: configuración no disponible.');
}

require_once $configPath;

// Protección contra ejecución directa
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    // Acceso directo permitido
} else {
    exit('Acceso no autorizado.');
}

// Logging de acceso técnico
$logPath = PATH_LOGS . 'accesos.log';
if (is_writable(dirname($logPath))) {
    file_put_contents($logPath, date('[Y-m-d H:i:s]') . " Acceso a portada\n", FILE_APPEND);
}

$version = defined('APP_VERSION') ? APP_VERSION : '1.0.0';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= APP_NAME ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="/matricula/public/assets/css/bootstrap.min.css?v=<?= $version ?>">
  <link rel="stylesheet" href="/matricula/public/assets/css/principal.css?v=<?= $version ?>">
  <link rel="stylesheet" href="/matricula/public/assets/icons/bootstrap-icons.css?v=<?= $version ?>">

  <style>
    body {
      background-attachment: fixed;
      background-size: cover;
    }
    
    .pie-portada {
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(5px);
      border-top: 1px solid rgba(0, 255, 255, 0.3);
      color: #00f2ff;
      padding: 15px 0;
    }

    /* EFECTO DE RESPLANDOR PARA LA FOTO */
    .sombra-portada-glow {
        box-shadow: 0 0 20px rgba(0, 242, 255, 0.5), 0 0 40px rgba(0, 242, 255, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(0, 242, 255, 0.3);
    }

    .sombra-portada-glow:hover {
        transform: scale(1.02);
        box-shadow: 0 0 30px rgba(0, 242, 255, 0.8), 0 0 60px rgba(0, 242, 255, 0.4);
    }
  </style>
</head>

<body class="fondo-futurista fuente-orbitron d-flex flex-column min-vh-100">

  <main class="flex-grow-1 container d-flex align-items-center">
    <div class="portada row justify-content-center align-items-center w-100">
      
      <div class="col-md-6 mb-4 mb-md-0">
        <img src="<?= PATH_IMG ?>registro-futurista.png" alt="Imagen portada" class="img-fluid rounded sombra-portada-glow">
      </div>

      <div class="col-md-6 contenido text-center">
        <h1 class="titulo-portada">Bienvenido al Futuro Educativo</h1>
        <p class="descripcion-portada mt-3">Un sistema moderno, dinámico y seguro para gestionar el proceso académico de forma inteligente.</p>
        
        <a href="<?= BASE_URL ?>modulos/views/login.php" class="btn btn-futurista mt-4">
            <i class="bi bi-cpu me-2"></i>Acceder
        </a>
      </div>

    </div>
  </main>

  <footer class="text-center pie-portada mt-auto">
    <div class="container">
      &copy; <?= date('Y') ?> <?= APP_AUTOR ?> · Todos los derechos reservados.
    </div>
  </footer>

</body>
</html>