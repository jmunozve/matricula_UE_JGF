<?php
// F:\xampp\htdocs\matricula\modulos\views\representante\index.php
include_once '../../../includes/header.php'; 
require_once '../../../includes/db.php';

$pdo = Conexion::abrir();

// Consulta para la tabla de representantes (Coherente con joins [cite: 2026-02-04])
$sql = "SELECT r.id_representante, r.cedula_rep, r.tipo_doc_rep, r.nombre_rep, r.apellido_rep, 
               r.tel_rep, p.nombre_parentesco 
        FROM representantes r
        LEFT JOIN parentescos p ON r.parentesco_rep = p.id_parentesco
        ORDER BY r.id_representante DESC";

$representantes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    /* Estilos para el scroll interno de la modal */
    #contenidoModalEditarRep {
        max-height: 85vh;
        overflow-y: auto !important;
        scrollbar-width: thin;
        scrollbar-color: #198754 #f8f9fc;
    }
    #contenidoModalEditarRep::-webkit-scrollbar { width: 8px; }
    #contenidoModalEditarRep::-webkit-scrollbar-thumb {
        background-color: #198754;
        border-radius: 10px;
    }
</style>

<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-people-fill me-2"></i>Gestión de Representantes</h5>
            <button class="btn btn-primary btn-sm" onclick="window.location.href='inscripcion_representante.php'">
                <i class="bi bi-plus-lg"></i> Nuevo Registro
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaRepresentantes">
                    <thead class="table-light">
                        <tr>
                            <th>Cédula</th>
                            <th>Nombre Completo</th>
                            <th>Parentesco</th>
                            <th>Teléfono</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($representantes as $row): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= $row['tipo_doc_rep'] ?>-<?= $row['cedula_rep'] ?></span></td>
                            <td><?= htmlspecialchars($row['nombre_rep'] . " " . $row['apellido_rep']) ?></td>
                            <td><?= $row['nombre_parentesco'] ?? 'N/A' ?></td>
                            <td><?= $row['tel_rep'] ?: 'S/N' ?></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="abrirModalEditarRep(<?= $row['id_representante'] ?>, '../../../')">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminarRep(<?= $row['id_representante'] ?>, '<?= $row['nombre_rep'] ?>')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarRepresentante" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
            <div id="contenidoModalEditarRep">
                </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleEstudiante" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" id="modalDetalleEstudianteContenido">
            </div>
    </div>
</div>

<?php include_once '../../../includes/footer.php'; ?>

<script>
// Mantener el uso de 'window.' asegura que las funciones sean accesibles desde la modal cargada por AJAX
window.abrirModalEditarRep = function(id, rutaBase = "") {
    const contenedor = $("#contenidoModalEditarRep");
    $("#modalEditarRepresentante").modal('show');
    
    contenedor.html(`
        <div class="modal-body text-center p-5">
            <div class="spinner-border text-success" style="width: 3.5rem; height: 3.5rem;" role="status"></div>
            <h4 class="mt-4 fw-bold text-dark">Sincronizando Datos</h4>
            <p class="text-muted">Recuperando expediente del sistema local...</p>
        </div>
    `);
    
    const urlDestino = `${rutaBase}modulos/views/representante/editar_representante.php?id=${id}`;
    
    $.ajax({
        url: urlDestino,
        method: 'GET',
        cache: false,
        success: function(response) { contenedor.html(response); },
        error: function(xhr) {
            contenedor.html(`<div class="p-5 text-center text-danger">Error ${xhr.status}: No se pudo cargar la vista.</div>`);
        }
    });
};

// 1. La función de guardado corregida
window.guardarCambiosRepresentante = function() {
    const form = $('#formActualizarRep')[0];
    if (!form) {
        console.error("No se encontró el formulario #formActualizarRep");
        return;
    }

    const formData = new FormData(form);
    const btn = $('#btnActualizarRep');

    // Cambiar estado visual
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

    $.ajax({
        // Ajustada la ruta: desde views/representante/ subes 2 niveles para llegar a modulos/
        url: '../../controllers/representante/actualizar_representante.php', 
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            console.log("Respuesta servidor:", res);
            try {
                const r = typeof res === 'string' ? JSON.parse(res) : res;
                if(r.status === 'success') {
                    Swal.fire({ 
                        icon: 'success', 
                        title: '¡Actualizado!', 
                        text: r.message, 
                        timer: 1500, 
                        showConfirmButton: false 
                    }).then(() => { location.reload(); });
                } else {
                    Swal.fire('Atención', r.message, 'warning');
                    btn.prop('disabled', false).html('<i class="bi bi-save2 me-1"></i> GUARDAR CAMBIOS');
                }
            } catch(e) {
                console.error("Error parseando JSON:", e, res);
                Swal.fire('Error', 'El servidor respondió con un error técnico.', 'error');
                btn.prop('disabled', false).html('GUARDAR CAMBIOS');
            }
        },
        error: function(xhr) {
            console.error("Error AJAX:", xhr);
            Swal.fire('Error', 'No se pudo conectar con el controlador.', 'error');
            btn.prop('disabled', false).html('GUARDAR CAMBIOS');
        }
    });
};

// 2. El disparador (Evento delegado)
// Este código debe ir al final de tus scripts en lista_representantes.php
$(document).on('click', '#btnActualizarRep', function(e) {
    e.preventDefault();
    
    // Si el botón tiene la clase 'disabled', no hacemos nada
    if ($(this).hasClass('disabled')) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Debe activar el switch de EDITAR primero',
            showConfirmButton: false,
            timer: 3000
        });
        return;
    }
    
    window.guardarCambiosRepresentante();
});

window.verDetalleEstudiante = function(idEstudiante) {
    // Corregido: Cargamos en el ID correcto de la segunda modal
    $("#modalDetalleEstudianteContenido").load('../../../includes/modal_detalle_seccion.php?id=' + idEstudiante, function() {
        $("#modalDetalleEstudiante").modal('show');
    });
};

window.confirmarEliminarRep = function(id, nombre) {
    Swal.fire({
        title: '¿Eliminar representante?',
        text: `Se borrará a ${nombre} y sus documentos asociados.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, borrar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('../../../modulos/controllers/representante/eliminar_representante.php', { id_representante: id }, function(res) {
                location.reload();
            });
        }
    });
};

$(document).on('click', '#btnActualizarRep', function(e) {
    e.preventDefault();
    // Verificamos que no tenga la clase disabled antes de ejecutar
    if (!$(this).hasClass('disabled')) {
        window.guardarCambiosRepresentante();
    }
});
</script>