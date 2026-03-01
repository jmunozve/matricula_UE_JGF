<?php
/**
 * modulos/views/estudiantes/buscar.php
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificamos que venimos de identificar a un representante
$id_rep = $_SESSION['ultimo_representante_id'] ?? '';
$nombre_rep = $_SESSION['ultimo_representante_nombre'] ?? '';
$cedula_rep = $_SESSION['ultimo_representante_cedula'] ?? '';
$tipo_rep = $_SESSION['ultimo_representante_tipo'] ?? '';

if (empty($id_rep)) {
    header("Location: ../representante/buscar.php");
    exit;
}

include_once "../../../includes/header.php";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div id="alertaContenedor"></div>

            <div class="card border-0 shadow-lg" style="border-radius: 15px;">
                <div class="card-header bg-success py-3 text-white text-center" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill me-2"></i> INSCRIPCIÓN DE ESTUDIANTE</h5>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-5">
                        <i class="bi bi-person-check-fill fs-2 me-3"></i>
                        <div>
                            <small class="d-block text-uppercase fw-bold text-muted">Representante Asignado:</small>
                            <span class="fs-5 fw-bold"><?= $nombre_rep ?></span> 
                            <span class="badge bg-primary ms-2"><?= $tipo_rep ?>-<?= $cedula_rep ?></span>
                        </div>
                    </div>

                    <div id="seccionPregunta" class="text-center">
                        <h4 class="fw-bold mb-4">¿El estudiante posee Cédula de Identidad?</h4>
                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" class="btn btn-outline-primary btn-lg px-5 shadow-sm" onclick="mostrarBuscador(true)">
                                <i class="bi bi-card-text me-2"></i> SÍ TIENE
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-lg px-5 shadow-sm" onclick="mostrarBuscador(false)">
                                <i class="bi bi-hash me-2"></i> NO TIENE
                            </button>
                        </div>
                    </div>

                    <div id="seccionBuscador" class="mt-4" style="display: none;">
                        <hr class="my-4">
                        <label id="labelBusqueda" class="form-label fw-bold text-secondary small text-uppercase"></label>
                        <div class="input-group input-group-lg shadow-sm">
                            <select id="tipo_doc_es" class="form-select flex-grow-0" style="width: 100px;">
                                <option value="V">V</option>
                                <option value="E">E</option>
                                <option value="P">P</option>
                                <option value="CE">CE</option>
                            </select>
                            <input type="text" id="cedula_estudiante" class="form-control" placeholder="Número de documento...">
                            <button class="btn btn-success fw-bold" type="button" id="btnVerificar">
                                <i class="bi bi-search me-1"></i> VERIFICAR
                            </button>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-link text-muted" onclick="window.location.reload()">
                                <i class="bi bi-arrow-left"></i> Cambiar opción
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNoEncontrado" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <i class="bi bi-exclamation-circle text-warning display-1"></i>
                <h3 class="fw-bold mt-3">No registrado</h3>
                <p class="text-muted fs-5">El estudiante <strong id="docVerificado"></strong> no se encuentra en el sistema.</p>
                <p class="mb-4">¿Desea proceder a registrar sus datos personales ahora?</p>
                <div class="d-grid gap-2">
                    <button type="button" id="btnIrARegistro" class="btn btn-success btn-lg fw-bold shadow-sm">
                        SÍ, REGISTRAR AHORA <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">CANCELAR</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once "../../../includes/footer.php"; ?>

<script>
// Declaramos la variable del modal fuera para que sea global
let bsModalNoEncontrado;

document.addEventListener('DOMContentLoaded', function() {
    // INICIALIZACIÓN CORRECTA DEL MODAL (Evita el error "bootstrap is not defined")
    bsModalNoEncontrado = new bootstrap.Modal(document.getElementById('modalNoEncontrado'));
    
    // Escuchar el clic del botón verificar
    document.getElementById('btnVerificar').addEventListener('click', realizarVerificacion);
});

let tieneCedula = true;

function mostrarBuscador(siTiene) {
    tieneCedula = siTiene;
    document.getElementById('seccionPregunta').style.display = 'none';
    document.getElementById('seccionBuscador').style.display = 'block';
    
    const label = document.getElementById('labelBusqueda');
    const select = document.getElementById('tipo_doc_es');
    const input = document.getElementById('cedula_estudiante');
    
    if (siTiene) {
        label.innerText = "Ingrese Cédula de Identidad del Estudiante";
        select.value = "V";
        select.disabled = false;
    } else {
        label.innerText = "Ingrese Cédula Escolar (CE)";
        select.value = "CE";
        select.disabled = true;
    }
    input.focus();
}

function realizarVerificacion() {
    const tipo = document.getElementById('tipo_doc_es').value;
    const cedulaInput = document.getElementById('cedula_estudiante').value.trim();

    if (cedulaInput.length < 5) {
        Swal.fire({
            icon: 'warning',
            title: 'Dato inválido',
            text: 'Por favor ingrese un número de documento válido.'
        });
        return;
    }

    // Loader de espera
    Swal.fire({
        title: 'Verificando...',
        didOpen: () => { Swal.showLoading(); }
    });

    const cedulaCompleta = `${tipo}-${cedulaInput}`;

    fetch('verificarestudiante.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `cedula=${cedulaCompleta}`
    })
    .then(res => res.json())
    .then(data => {
        Swal.close(); // Cerramos el loader

        if (data.existe) {
            Swal.fire({
                icon: 'info',
                title: 'Estudiante ya registrado',
                html: `El estudiante <b>${data.nombre} ${data.apellido}</b> ya se encuentra en el sistema.<br><br>Representante: <b>${data.nombre_representante}</b>`,
                confirmButtonText: 'Ver Perfil',
                showCancelButton: true,
                cancelButtonText: 'Cerrar'
            });
        } else {
            document.getElementById('docVerificado').innerText = cedulaCompleta;
            bsModalNoEncontrado.show();
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo verificar en el servidor local.'
        });
    });
}

document.getElementById('btnIrARegistro').addEventListener('click', function() {
    const tipo = document.getElementById('tipo_doc_es').value;
    const cedula = document.getElementById('cedula_estudiante').value.trim();
    window.location.href = `registro.php?cedula=${cedula}&tipo=${tipo}`;
});
</script>