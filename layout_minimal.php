<?php
// 1. GESTIÓN DE SESIÓN Y SEGURIDAD
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * LÓGICA DE PROTECCIÓN DE RUTA - SIN "AUTENTICACION"
 */
// Obtenemos la vista actual de la sesión o definimos el dashboard por defecto
$vista_actual = $_SESSION['vista_actual'] ?? 'modulos/views/dashboard.php';

// Si no hay sesión iniciada y no estamos intentando cargar el login, redirigir al login
if (!isset($_SESSION['usuario_id']) && $vista_actual !== 'modulos/views/login.php') {
    header("Location: modulos/views/login.php?mensaje=Sesi%C3%B3n%20requerida");
    exit();
}

ob_start();

// 2. CONFIGURACIÓN DE RUTAS
$version   = date('YmdHis'); 
$titulo    = $_SESSION['titulo_pagina'] ?? 'Sistema de Matrícula';
$base_path = __DIR__;

// 3. VALIDACIÓN DE VISTA DINÁMICA
// Se construye la ruta absoluta para Windows usando DIRECTORY_SEPARATOR (\)
$ruta_final = $base_path . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $vista_actual);

if (file_exists($ruta_final) && !is_dir($ruta_final)) {
    $contenido_vista = $ruta_final;
} else {
    // Ruta corregida para el error 404
    $contenido_vista = $base_path . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'error' . DIRECTORY_SEPARATOR . '404.php';
}

$sidebarCollapsed = $_SESSION['sidebarCollapsed'] ?? false;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titulo) ?> — U.E.D José Gil Fortoul</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="/matricula/public/assets/css/bootstrap.min.css?v=<?= $version ?>">
    <link rel="stylesheet" href="/matricula/public/assets/css/custom.css?v=<?= $version ?>">
    <link rel="stylesheet" href="/matricula/public/assets/icons/bootstrap-icons.css?v=<?= $version ?>">
</head>
<body class="app-container <?= ($sidebarCollapsed || $vista_actual === 'modulos/views/login.php') ? 'sidebar-collapsed' : '' ?>">

    <?php 
        // Solo incluir componentes de navegación si NO es la página de login
        if ($vista_actual !== 'modulos/views/login.php') {
            if(file_exists($base_path . '/includes/menu.php')) include $base_path . '/includes/menu.php'; 
            if(file_exists($base_path . '/includes/header.php')) include $base_path . '/includes/header.php'; 
        }
    ?>

    <main id="main-content" class="main-content">
        <div class="safe-area p-3 <?= ($sidebarCollapsed || $vista_actual === 'modulos/views/login.php') ? 'expanded' : '' ?>">
            <?php 
                if (file_exists($contenido_vista)) {
                    include $contenido_vista;
                } else {
                    echo "<div class='alert alert-danger shadow-sm'>
                            <i class='bi bi-exclamation-octagon-fill me-2'></i>
                            <b>Error de Sistema:</b> No se pudo localizar la vista <code>$vista_actual</code>.
                          </div>";
                }
            ?>
        </div>
    </main>

    <div class="modal fade" id="modalNotificacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div id="modalContentBorder" class="modal-content border-3">
                <div id="modalHeaderBg" class="modal-header text-white">
                    <h5 class="modal-title" id="modalNotificacionLabel">Notificación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body fs-5 text-center py-4" id="modalNotificacionMensaje"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/matricula/public/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarModal(titulo, mensaje, tipo = 'success') {
            const modalEl = document.getElementById('modalNotificacion');
            if(!modalEl) return;
            
            document.getElementById('modalNotificacionLabel').textContent = titulo;
            document.getElementById('modalNotificacionMensaje').innerHTML = mensaje;

            const estilos = {
                success: { border: 'border-success', bg: 'bg-success' },
                error:   { border: 'border-danger',  bg: 'bg-danger'  },
                warning: { border: 'border-warning', bg: 'bg-warning' },
                info:    { border: 'border-info',    bg: 'bg-info'    }
            };

            const estilo = estilos[tipo] || estilos.success;
            document.getElementById('modalContentBorder').className = `modal-content border-3 ${estilo.border}`;
            document.getElementById('modalHeaderBg').className = `modal-header ${estilo.bg} text-white`;

            new bootstrap.Modal(modalEl).show();
        }
    </script>

    <?php if (isset($_SESSION['mensaje'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            mostrarModal(
                "<?= ($_SESSION['tipo'] ?? 'success') === 'error' ? 'Operación Fallida' : 'Operación Exitosa' ?>",
                "<?= addslashes($_SESSION['mensaje']) ?>",
                "<?= $_SESSION['tipo'] ?? 'success' ?>"
            );
        });
    </script>
    <?php unset($_SESSION['mensaje'], $_SESSION['tipo']); endif; ?>

</body>
</html>
<?php ob_end_flush(); ?>