<?php
// =================================================================
// modulos/views/dashboard.php - PANEL PRINCIPAL CORREGIDO
// =================================================================

// 1. Iniciamos sesión y definimos la ruta ANTES del header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['ruta_activa'] = 'dashboard';

// 2. Cargamos el header
require_once dirname(__DIR__, 2) . '/includes/header.php';

// 3. Definición de rutas y captura de datos de sesión
$views_path = "/matricula/modulos/views/";
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Corrección: Usamos trim() y strtolower() para asegurar que la comparación sea exacta
$rol_usuario = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : 'administrativo';
?>

<style>
    /* Forzar que el footer baje al final */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .dashboard-container {
        flex: 1 0 auto;
        padding-top: 20px;
        padding-bottom: 60px;
    }

    /* Estilos de Tarjetas */
    .card-accent-top {
        border: 0 !important;
        border-top: 5px solid !important;
        border-radius: 20px !important;
    }

    .accent-blue {
        border-top-color: #0d6efd !important;
    }

    .accent-orange {
        border-top-color: #fd7e14 !important;
    }

    .accent-green {
        border-top-color: #198754 !important;
    }

    .accent-cyan {
        border-top-color: #0dcaf0 !important;
    }

    .accent-purple {
        border-top-color: #6f42c1 !important;
    }

    .icon-feature {
        font-size: 3.5rem;
        transition: transform 0.3s ease;
        line-height: 1;
    }

    .card-hover:hover .icon-feature {
        transform: scale(1.15) rotate(5deg);
    }

    .bg-soft-blue {
        background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
        color: #0d6efd;
    }

    .bg-soft-orange {
        background: linear-gradient(135deg, #ffd89b 0%, #ffb75e 100%);
        color: #fd7e14;
    }

    .bg-soft-green {
        background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
        color: #198754;
    }

    .bg-soft-cyan {
        background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        color: #0dcaf0;
    }

    .bg-soft-purple {
        background: linear-gradient(135deg, #efecf8 0%, #e2d9f3 100%);
        color: #6f42c1;
    }

    .icon-circle {
        width: 110px;
        height: 110px;
        border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
    }

    .card-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card-hover:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
    }

    .btn-custom {
        border-radius: 10px;
        padding: 12px 20px;
        font-weight: 600;
        text-decoration: none;
    }
</style>

<div class="container-fluid dashboard-container">
    <div class="row mb-5 text-center">
        <div class="col-12">
            <h1 class="fw-bold text-dark display-5">Gestión de Matrícula</h1>
            <p class="lead text-muted">Bienvenido, <strong><?= htmlspecialchars($nombre_usuario) ?></strong> - Panel U.E.D. <strong>José Gil Fortoul</strong></p>
            <div class="mx-auto" style="width: 60px; height: 4px; background: #0d6efd; border-radius: 2px;"></div>
        </div>
    </div>

    <div class="row g-4 px-md-4 justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm card-hover card-accent-top accent-blue">
                <div class="card-body p-4 text-center">
                    <div class="icon-circle bg-soft-blue"><i class="bi bi-person-plus icon-feature"></i></div>
                    <h4 class="fw-bold mb-3">Registro y Carga</h4>
                    <p class="text-muted mb-4 px-3">Gestión de Estudiantes y Secciones</p>
                    <div class="d-grid gap-2">
                        <a href="<?= $views_path ?>secciones/lista.php" class="btn btn-outline-primary btn-custom border-2">
                            <i class="bi bi-people-fill me-1"></i> Gestión de Estudiantes
                        </a>
                        <a href="<?= $views_path ?>representante/lista_representantes.php" class="btn btn-outline-success btn-custom border-2">
                            <i class="bi bi-person-badge me-1"></i> Gestión de Representantes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm card-hover card-accent-top accent-orange">
                <div class="card-body p-4 text-center">
                    <div class="icon-circle bg-soft-orange"><i class="bi bi-shield-check icon-feature"></i></div>
                    <h4 class="fw-bold mb-3">Función Directiva</h4>
                    <p class="text-muted mb-4 px-3">Monitor de planta física y disponibilidad de cupos.</p>
                    <div class="d-grid">
                        <a href="<?= $views_path ?>directivo/resumen_cupos.php" class="btn btn-warning btn-custom py-3 text-white">
                            <i class="bi bi-diagram-3-fill me-2"></i>Resumen de Cupos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $roles_supervisores = ['coordinador', 'coordinadora', 'director', 'directora', 'admin', 'superusuario'];
        if (in_array($rol_usuario, $roles_supervisores)):
        ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm card-hover card-accent-top accent-green">
                    <div class="card-body p-4 text-center">
                        <div class="icon-circle bg-soft-green mb-4 mx-auto"><i class="bi bi-check2-all icon-feature text-success"></i></div>
                        <h4 class="fw-bold mb-3">Validación Académica</h4>
                        <p class="text-muted mb-4 px-3">Verificación y cambio de estatus de matrícula.</p>
                        <div class="d-grid">
                            <a href="<?= $views_path ?>academico/gestion_central.php" class="btn btn-success btn-custom py-3 shadow-sm">
                                <i class="bi bi-patch-check-fill me-2"></i>Validar Registros
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm card-hover card-accent-top accent-cyan">
                <div class="card-body p-4 text-center">
                    <div class="icon-circle bg-soft-cyan"><i class="bi bi-file-earmark-bar-graph icon-feature"></i></div>
                    <h4 class="fw-bold mb-3">Centro de Reportes</h4>
                    <p class="text-muted mb-4 px-3">Emisión de constancias y listados oficiales.</p>
                    <div class="d-grid">
                        <a href="<?= $views_path ?>reportes/index.php" class="btn btn-info btn-custom py-3 text-white">
                            <i class="bi bi-printer-fill me-2"></i>Generar Documentos
                        </a>
                    </div>
                </div>
            </div>
        </div>


<?php if ($rol_usuario === 'superusuario' || $rol_usuario === 'admin'): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm card-hover card-accent-top accent-purple">
            <div class="card-body p-4 text-center">
                <div class="icon-circle bg-soft-purple"><i class="bi bi-cpu icon-feature"></i></div>
                <h4 class="fw-bold mb-3">Mantenimiento</h4>
                <p class="text-muted mb-4 px-3">Gestión de usuarios y configuración del sistema.</p>
                <div class="d-grid">
                    <a href="http://localhost/matricula/modulos/views/admin/usuarios/configuracion.php" class="btn btn-dark btn-custom py-3">
                        <i class="bi bi-gear-fill me-2"></i>Configuración Sistema
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
    </div>
</div>

<?php
require_once dirname(__DIR__, 2) . '/includes/footer.php';
?>