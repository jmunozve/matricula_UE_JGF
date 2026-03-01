<?php
session_start();

// 1. CARGA DE CONFIGURACIÓN (Necesario para APP_AUTOR)
$baseDir = dirname(dirname(__DIR__));
$configPath = $baseDir . '/includes/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

// 2. REDIRECCIÓN SI YA ESTÁ LOGUEADO
if (isset($_SESSION['id_usuario']) && isset($_SESSION['rol'])) {
    header("Location: /matricula/modulos/views/dashboard.php");
    exit();
}

// 3. CONEXIÓN A LA BASE DE DATOS
require_once('../../includes/db.php'); 

try {
    $conn = Conexion::abrir();
} catch (Exception $e) {
    error_log("Error de conexión: " . $e->getMessage());
}

// 4. LIMPIEZA AUTOMÁTICA DE CARPETA TEMP
function limpiarCarpetaTemp() {
    $dir_temp = 'F:/xampp/htdocs/matricula/public/uploads/temp/'; 
    if (is_dir($dir_temp)) {
        $archivos = glob($dir_temp . "*");
        $ahora = time();
        $segundos_limite = 600; // 10 minutos

        foreach ($archivos as $archivo) {
            if (is_file($archivo) && ($ahora - filemtime($archivo) > $segundos_limite)) {
                @unlink($archivo);
            }
        }
    }
}
limpiarCarpetaTemp();

// 5. MANEJO DE MENSAJES
$mensaje = $_SESSION['mensaje_login'] ?? '';
unset($_SESSION['mensaje_login']);

$version = '1.0.2'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión | Sistema Gil Fortoul</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="/matricula/public/assets/css/bootstrap.min.css?v=<?= $version ?>">
    <link rel="stylesheet" href="/matricula/public/assets/css/principal.css?v=<?= $version ?>"> 
    <link rel="stylesheet" href="/matricula/public/assets/css/login.css?v=<?= $version ?>">
    <link rel="stylesheet" href="/matricula/public/assets/css/bootstrap-icons.min.css?v=<?= $version ?>">

    <style>
        /* Estilos necesarios para que el footer baje al final */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pie-portada {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            border-top: 1px solid rgba(0, 255, 255, 0.3);
            color: #00f2ff;
            padding: 15px 0;
        }
    </style>
</head>

<body class="fondo-futurista fuente-orbitron">

    <main class="container">
        <div class="login-card w-100" style="max-width: 420px;">
            
            <div class="text-center mb-4">
                <h4 class="mt-2">🔐 Acceso al Sistema</h4>
                <small class="text-muted">Control de Matrícula v<?= $version ?></small>
            </div>

            <?php if ($mensaje): ?>
                <div class="alert alert-info text-center py-2 small">
                    <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/matricula/modulos/controllers/LoginControllers.php" novalidate>
                <div class="mb-3">
                    <label class="form-label">Cédula de Usuario:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                        <input type="text" name="cedula" class="form-control" 
                               placeholder="Ej: 12345678" required 
                               pattern="\d+" inputmode="numeric" 
                               title="Solo números" autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="contrasena" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="text-end mt-1">
                        <a href="#" class="text-decoration-none small" style="font-size: 0.75rem;">¿Olvidó su clave?</a>
                    </div>
                </div>

                <button type="submit" class="btn btn-futurista w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>INGRESAR
                </button>
            </form>

            <div class="text-center mt-2">
                <img src="/matricula/public/assets/img/logo.png" alt="Logo Gil Fortoul" style="max-width: 130px; height: 120px; background: white; padding: 5px; border-radius: 4px;">
            </div>
        </div>
    </main>

    <footer class="text-center pie-portada mt-auto">
        <div class="container">
            &copy; <?= date('Y') ?> <?= defined('APP_AUTOR') ? APP_AUTOR : 'U.E. "Gil Fortoul"' ?> · Todos los derechos reservados.
        </div>
    </footer>

    <script src="/matricula/public/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        const inactividadTiempo = 10 * 60 * 1000;
        let temporizador;
        function reiniciarTemporizador() {
            clearTimeout(temporizador);
            temporizador = setTimeout(() => {
                window.location.reload(); 
            }, inactividadTiempo);
        }
        ['mousemove', 'keypress', 'click', 'touchstart'].forEach(evt => {
            window.addEventListener(evt, reiniciarTemporizador);
        });
        reiniciarTemporizador();
    </script>
</body>
</html>