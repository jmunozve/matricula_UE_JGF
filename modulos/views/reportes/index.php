<?php
// 1. Configuración del Breadcrumb
$breadcrumb_custom = [
    ['nombre' => 'Reportes', 'ruta' => 'reportes/index']
];

// 2. Inclusión de cabecera y base de datos
include_once "../../../includes/header.php";
require_once "../../../includes/db.php";

$pdo = Conexion::abrir();
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-dark">
                <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Centro de Reportes
            </h2>
            <p class="text-muted">Generación de documentos oficiales en pestañas independientes.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-primary border-4">
                <div class="card-body text-center p-4">
                    <i class="bi bi-people-fill fs-1 text-primary mb-3"></i>
                    <h5 class="fw-bold">Listado General</h5>
                    <p class="small text-muted">Lista completa de estudiantes por grado/sección.</p>
                    <div class="d-grid mt-4">
                        <a href="javascript:void(0);" onclick="abrirReporte('listado_general.php')" class="btn btn-primary">
                            <i class="bi bi-printer me-2"></i>Imprimir Todo
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-success border-4">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-person-badge-fill fs-1 text-success mb-3"></i>
                    <h5 class="fw-bold">Ficha Individual</h5>
                    <p class="small text-muted">Planilla oficial con datos académicos y del representante.</p>
                    <div class="input-group mt-4">
                        <input type="text" id="ci_estudiante" class="form-control border-success" placeholder="Cédula o Escolar" onkeypress="if(event.key === 'Enter') generarFichaIndividual()">
                        <button class="btn btn-success" type="button" onclick="generarFichaIndividual()">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-top border-secondary border-4 opacity-75">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-graph-up fs-1 text-secondary mb-3"></i>
                    <h5 class="fw-bold">Estadísticas</h5>
                    <p class="small text-muted">Análisis de matrícula y procedencia.</p>
                    <div class="d-grid mt-4">
                        <button class="btn btn-outline-secondary disabled">Próximamente</button>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>

<script>
/**
 * Abre la ficha individual en una pestaña nueva
 */
function generarFichaIndividual() {
    const ci = document.getElementById('ci_estudiante').value.trim();
    if (ci === "") {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese una cédula o ID escolar.' });
        return;
    }
    
    // El segundo parámetro '_blank' fuerza la nueva pestaña
    window.open(`../../controllers/reportes/ficha_individual.php?id=${ci}`, '_blank');
}

/**
 * Función genérica para abrir otros reportes en pestañas nuevas
 * Evita el error 404 mostrando una alerta si el archivo no está listo
 */
function abrirReporte(archivo) {
    // Aquí puedes validar si el archivo existe antes de abrirlo
    // Por ahora, como estamos en desarrollo, usamos la alerta:
    Swal.fire({
        title: '¿Generar Listado?',
        text: "Se abrirá el reporte general en una nueva pestaña.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, abrir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Si el archivo existiera: window.open(`../../controllers/reportes/${archivo}`, '_blank');
            // Como aún no lo creamos, avisamos:
            Swal.fire('Información', 'El archivo listado_general.php se habilitará tras configurar los filtros por grado.', 'info');
        }
    });
}
</script>

<?php include_once "../../../includes/footer.php"; ?>