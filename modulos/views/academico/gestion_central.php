<?php
/**
 * F:\xampp\htdocs\matricula\modulos\views\academico\gestion_central.php
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/header.php';

// Seguridad: Solo Directivos y Admin (Tu Portero)
$roles_permitidos = ['directora', 'coordinador', 'admin'];
if (!isset($rol) || !in_array(strtolower($rol), $roles_permitidos)) {
    echo "<script>window.location.href='/matricula/modulos/views/dashboard.php?error=acceso_denegado';</script>";
    exit;
}
?>

<div class="container-fluid px-4 py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-5 fw-bold text-dark">Gestión Académica Central</h1>
            <p class="text-muted fs-5">Administración centralizada de matrículas, expedientes y representantes.</p>
            <hr class="mx-auto" style="width: 100px; border: 2px solid #0d6efd;">
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm border-0 border-top border-4 border-success card-hover">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="mb-4 text-success bg-success-subtle d-inline-flex p-3 rounded-4">
                        <i class="bi bi-shield-check fs-1"></i>
                    </div>
                    <h4 class="fw-bold">Validar Inscripciones</h4>
                    <p class="text-muted flex-grow-1">Control de pre-inscritos. Aquí apruebas el ingreso, asignas sección y verificas documentos. (Sin edición profunda).</p>
                    <a href="../estudiante/validar_inscripciones.php" class="btn btn-success rounded-pill w-100 py-2 fw-bold">
                        Abrir Proceso <i class="bi bi-arrow-right-short ms-1"></i>
                    </a>
                </div>
            </div>
        </div>



<style>
    .card-hover {
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
    }
    .card-hover:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important;
    }
    .bg-success-subtle { background-color: #d1e7dd; }
    .bg-primary-subtle { background-color: #cfe2ff; }
    .bg-warning-subtle { background-color: #fff3cd; }
    .rounded-4 { border-radius: 1rem !important; }
</style>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/footer.php'; ?>