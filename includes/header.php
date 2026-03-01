<?php
/**
 * includes/header.php - SISTEMA GIL FORTOUL
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- DATOS DE USUARIO ---
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Usuario';
$rol = $_SESSION['rol'] ?? 'usuario';

// --- RUTAS BASE ---
$base_url = "/matricula/";
$css_path = $base_url . "public/assets/css/";
$js_path  = $base_url . "public/assets/js/";
$views_path = $base_url . "modulos/views/";

/*
 LÓGICA DE BREADCRUMB - CORRECCIÓN DE PERSISTENCIA

// Detectamos en qué archivo físico estamos parados realmente
$archivo_actual = basename($_SERVER['PHP_SELF']);

// Si físicamente estamos en dashboard.php, forzamos la limpieza de la sesión
if ($archivo_actual === 'dashboard.php') {
    $_SESSION['ruta_activa'] = 'dashboard';
}

$ruta_activa = $_SESSION['ruta_activa'] ?? 'dashboard';
$items_finales = [];
$views_path = $base_url . "modulos/views/";

if ($ruta_activa !== 'dashboard') {
    if (isset($breadcrumb_custom)) {
        $items_finales = $breadcrumb_custom;
    } else {
        $ruta_segmentos = explode('/', $ruta_activa);
        $acumulador = '';
        foreach ($ruta_segmentos as $segmento) {
            if (empty($segmento) || $segmento === 'dashboard') continue;
            
            $acumulador .= ($acumulador ? '/' : '') . $segmento;
            $items_finales[] = [
                'nombre' => ucfirst(str_replace('_', ' ', $segmento)),
                'ruta' => $acumulador // Guardamos solo la estructura de carpetas
            ];
        }
    }
} */

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Gil Fortoul</title>

    <link rel="stylesheet" href="<?= $css_path ?>bootstrap.min.css">
    <link rel="stylesheet" href="<?= $css_path ?>bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $css_path ?>sweetalert2.min.css">
    
    <style>
        :root {
            --header-height: 60px;
            --breadcrumb-height: 40px;
            --primary-color: #0d6efd;
        }

        html, body { height: 100%; margin: 0; }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f4f7f6;
            padding-top: calc(var(--header-height) + var(--breadcrumb-height) + 10px);
        }

        .header-fixed {
            position: fixed; top: 0; left: 0; width: 100%; 
            height: var(--header-height); z-index: 1030; 
            background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .breadcrumb-fixed {
            position: fixed; top: var(--header-height); left: 0; 
            width: 100%; height: var(--breadcrumb-height);
            z-index: 1020; background-color: #f8f9fa;
            font-size: 0.85rem; display: flex; align-items: center;
        }

        .main-content { flex: 1 0 auto; width: 100%; }

        .breadcrumb-link { text-decoration: none; color: #6c757d; display: flex; align-items: center; }
        .breadcrumb-link:hover { color: var(--primary-color); }
        .breadcrumb-separator { color: #adb5bd; display: flex; align-items: center; padding: 0 8px; }

        .swal2-container { z-index: 9999 !important; }
        
        .form-label, label {
            color: #000000 !important;
            font-weight: 600 !important;
        }

        .req {
            color: #dc3545 !important;
            font-weight: bold;
            margin-right: 3px;
        }
    </style>
</head>
<body>

<header class="header-fixed border-bottom">
    <div class="d-flex justify-content-between align-items-center h-100 px-4">
        
        <a href="<?= $views_path ?>dashboard.php" class="btn btn-sm btn-outline-primary me-3" title="Ir al Dashboard">
            <i class="bi bi-grid-fill"></i>
        </a>

        <div class="flex-grow-1 text-center">
            <h6 class="mb-0 text-primary fw-bold text-uppercase">Sistema Gil Fortoul</h6>
        </div>

        <div class="dropdown">
            <button class="btn btn-light border btn-sm dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-2 text-primary"></i> 
                <span class="d-none d-sm-inline"><?= htmlspecialchars($nombre_usuario) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li class="px-3 py-1 small text-muted font-monospace">Rol: <?= htmlspecialchars(ucfirst($rol)) ?></li>
                <li><hr class="dropdown-divider"></li>
                
                <?php if (strtolower($rol) === 'admin'): ?>
                    <li><a class="dropdown-item" href="<?= $views_path ?>admin/usuarios/configuracion.php"><i class="bi bi-gear me-2"></i>Configuración</a></li>
                <?php endif; ?>
                
                <li><a class="dropdown-item text-danger" href="<?= $views_path ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
            </ul>
        </div>
    </div>
</header>

<nav class="breadcrumb-fixed border-bottom px-4">
    <a href="<?= $views_path ?>dashboard.php" class="breadcrumb-link">
        <i class="bi bi-house-door-fill text-primary me-1"></i> Dashboard
    </a>

    <?php if (!empty($items_finales)): ?>
        <?php foreach ($items_finales as $index => $item): ?>
            <?php 
                // Evitamos duplicar 'dashboard' si viene en la ruta
                if (strtolower($item['nombre']) === 'dashboard') continue;

                $url = $views_path . $item['ruta'] . ".php";
                $es_ultimo = ($index == count($items_finales) - 1);
            ?>
            <span class="breadcrumb-separator"><i class="bi bi-chevron-right small"></i></span>
            
            <?php if ($es_ultimo): ?>
                <span class="fw-bold text-dark"><?= htmlspecialchars($item['nombre']) ?></span>
            <?php else: ?>
                <a href="<?= $url ?>" class="breadcrumb-link">
                    <?= htmlspecialchars($item['nombre']) ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</nav>

<div class="main-content">