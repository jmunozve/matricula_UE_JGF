<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['ruta_activa'] = '/configuracion';

/**
 * 1. Verificación de Seguridad Flexible
 * CORRECCIÓN: Se añade 'admin' a la lista de roles permitidos.
 */
$rol_actual = isset($_SESSION['rol']) ? strtolower(trim((string)$_SESSION['rol'])) : '';

// Lista ampliada para que el administrador también pueda configurar el sistema
$roles_permitidos = ['superusuario', 'admin']; 

if (!in_array($rol_actual, $roles_permitidos)) {
    header("Location: /matricula/modulos/views/dashboard.php?error=acceso_denegado");
    exit();
}

// 2. Título y Breadcrumbs para el Header
$titulo_pagina = "Configuración del Sistema";
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/header.php';
?>

<style>
    .config-card {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        background: #fff;
    }

    .config-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .icon-box {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .bg-light-primary {
        background-color: #e7f1ff;
        color: #0d6efd;
    }

    .bg-light-success {
        background-color: #e6fcf5;
        color: #20c997;
    }

    .bg-light-warning {
        background-color: #fff9db;
        color: #fab005;
    }

    .bg-light-danger {
        background-color: #fff5f5;
        color: #ff6b6b;
    }

    .bg-light-purple {
        background-color: #f3e5f5;
        color: #8e24aa;
    }
</style>

<link rel="stylesheet" href="/matricula/public/assets/css/sweetalert2.min.css">

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/matricula/modulos/views/dashboard.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Configuración</li>
                </ol>
            </nav>
            <h2 class="fw-bold"><i class="bi bi-gear-fill me-2 text-secondary"></i> Ajustes Generales</h2>
            <p class="text-muted">Administre los parámetros globales del sistema y el control institucional.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm config-card">
                <div class="card-body p-4 text-center text-lg-start">
                    <div class="icon-box bg-light-purple mx-auto mx-lg-0">
                        <i class="bi bi-person-workspace fs-3"></i>
                    </div>
                    <h5 class="fw-bold">Gestión de Personal</h5>
                    <p class="text-muted small">Administre la información de Directivos, Docentes, Administrativos y Obreros de la institución.</p>
                    <a href="/matricula/modulos/views/admin/personal/lista_personal.php" class="btn btn-purple w-100 rounded-pill mt-3 shadow-sm text-white" style="background-color: #8e24aa;">
                        <i class="bi bi-person-lines-fill me-2"></i>Administrar Talento Humano
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm config-card">
                <div class="card-body p-4 text-center text-lg-start">
                    <div class="icon-box bg-light-primary mx-auto mx-lg-0">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                    <h5 class="fw-bold">Gestión de Usuarios</h5>
                    <p class="text-muted small">Crear, editar y dar de baja cuentas de Administrativos, Docentes y Representantes.</p>
                    <a href="usuarios_lista.php" class="btn btn-primary w-100 rounded-pill mt-3 shadow-sm">
                        <i class="bi bi-person-gear me-2"></i>Administrar Usuarios
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm config-card">
                <div class="card-body p-4 text-center text-lg-start">
                    <div class="icon-box bg-light-success mx-auto mx-lg-0">
                        <i class="bi bi-building-fill-gear fs-3"></i>
                    </div>
                    <h5 class="fw-bold">Datos de la Institución</h5>
                    <p class="text-muted small">Configurar la identidad del plantel: Logo, Código DEA, Periodo Escolar y Contactos.</p>
                    <a href="/matricula/modulos/views/admin/institucion/datos_plantel.php" class="btn btn-success w-100 rounded-pill mt-3 shadow-sm text-white">
                        <i class="bi bi-pencil-square me-2"></i>Editar Información
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm config-card">
                <div class="card-body p-4 text-center text-lg-start">
                    <div class="icon-box bg-light-warning mx-auto mx-lg-0">
                        <i class="bi bi-shield-lock-fill fs-3"></i>
                    </div>
                    <h5 class="fw-bold">Seguridad</h5>
                    <p class="text-muted small">Ver registros de acceso, respaldar la base de datos y depurar archivos temporales.</p>
                    <button class="btn btn-warning w-100 rounded-pill mt-3 shadow-sm text-dark fw-semibold"
                        onclick="Swal.fire({
                                title: 'Mantenimiento', 
                                text: 'El módulo de Seguridad y Respaldo está en desarrollo.', 
                                icon: 'info',
                                confirmButtonColor: '#fab005'
                            })">
                        <i class="bi bi-tools me-2"></i>Herramientas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 text-center">
        <a href="/matricula/modulos/views/dashboard.php" class="btn btn-outline-secondary px-5 rounded-pill shadow-sm">
            <i class="bi bi-arrow-left me-2"></i> Volver al Panel Principal
        </a>
    </div>
</div>

<script src="/matricula/public/assets/js/sweetalert2.all.min.js"></script>

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/footer.php';
?>