<?php
// F:\xampp\htdocs\matricula\modulos\views\representante\editar_representante.php
require_once dirname(__DIR__, 3) . '/includes/db.php';
$pdo = Conexion::abrir();

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$stmt = $pdo->prepare("SELECT * FROM representantes WHERE id_representante = ?");
$stmt->execute([$id]);
$rep = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rep) die("<div class='p-4 text-center text-danger fw-bold'>Representante no encontrado en la base de datos.</div>");

// Carga de tablas maestras
$paises = $pdo->query("SELECT * FROM paises ORDER BY nombre_pais ASC")->fetchAll();
$nacionalidades = $pdo->query("SELECT * FROM nacionalidades ORDER BY nombre_nacionalidad ASC")->fetchAll();
$parentescos = $pdo->query("SELECT * FROM parentescos ORDER BY id_parentesco ASC")->fetchAll();
$estados = $pdo->query("SELECT * FROM estados ORDER BY nombre_estado ASC")->fetchAll();

$municipios = $rep['id_estado_rep'] ? $pdo->query("SELECT * FROM municipios WHERE id_estado = {$rep['id_estado_rep']} ORDER BY nombre_municipio ASC")->fetchAll() : [];
$parroquias = $rep['id_municipio_rep'] ? $pdo->query("SELECT * FROM parroquias WHERE id_municipio = {$rep['id_municipio_rep']} ORDER BY nombre_parroquia ASC")->fetchAll() : [];

$stmtHijos = $pdo->prepare("SELECT id_estudiante, nombre_es, apellido_es, cedula_es, cedula_escolar, tipo_doc_es, fecha_nacimiento, nombre_grado, nombre_seccion FROM vista_estudiantes_completa WHERE id_representante = ?");
$stmtHijos->execute([$id]);
$hijos = $stmtHijos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="modal-header bg-dark text-white py-2 shadow-sm">
    <h6 class="modal-title d-flex align-items-center">
        <i class="bi bi-person-badge-fill me-2 text-success"></i>
        EXPEDIENTE: <?= strtoupper($rep['nombre_rep'] . " " . $rep['apellido_rep']) ?>
    </h6>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="alert alert-warning border-0 mx-3 mt-3 py-2 mb-0 shadow-sm d-flex justify-content-between align-items-center">
    <div class="small text-dark">
        <i class="bi bi-shield-lock-fill me-2"></i><strong>Modo Lectura:</strong> Active el switch para modificar.
    </div>
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="switchEditarRep" style="cursor:pointer">
        <label class="form-check-label fw-bold small text-primary" for="switchEditarRep">EDITAR</label>
    </div>
</div>

<div class="modal-body pt-3">
    <ul class="nav nav-pills nav-fill mb-3 bg-light p-1 rounded border" id="tabRep" role="tablist">
        <li class="nav-item"><button class="nav-link active py-1 small" data-bs-toggle="tab" data-bs-target="#basicos">D. Básicos</button></li>
        <li class="nav-item"><button class="nav-link py-1 small" data-bs-toggle="tab" data-bs-target="#ubicacion">Ubicación</button></li>
        <li class="nav-item"><button class="nav-link py-1 small" data-bs-toggle="tab" data-bs-target="#documentos">Documentos</button></li>
        <li class="nav-item"><button class="nav-link py-1 small fw-bold text-success" data-bs-toggle="tab" data-bs-target="#tabHijosPane">Hijos (<?= count($hijos) ?>)</button></li>
    </ul>

    <div class="tab-content border rounded p-3 bg-white shadow-sm" style="min-height: 320px;">
        <div class="tab-pane fade show active" id="basicos">
            <form id="formActualizarRep">
                <input type="hidden" name="id_representante" value="<?= $rep['id_representante'] ?>">
                <fieldset id="fieldRep" disabled>
                    <div class="row g-3">
                        <div class="col-md-3 text-center border-end">
                            <?php $foto = !empty($rep['foto_carnet_rep']) ? "uploads/fotos_reps/" . $rep['foto_carnet_rep'] : "public/assets/img/default-user.png"; ?>
                            <img id="m_pre_foto" src="../../../<?= $foto ?>?v=<?= time() ?>" class="img-thumbnail shadow-sm mb-2" style="width: 120px; height: 140px; object-fit: cover;">
                            <input type="file" name="foto_carnet" class="form-control form-control-sm" onchange="m_previewImg(this)">
                        </div>
                        <div class="col-md-9">
                            <div class="row g-2">
                                <div class="col-md-6"><label class="small fw-bold">Nombres</label><input type="text" name="nombre_rep" class="form-control form-control-sm" value="<?= $rep['nombre_rep'] ?>"></div>
                                <div class="col-md-6"><label class="small fw-bold">Apellidos</label><input type="text" name="apellido_rep" class="form-control form-control-sm" value="<?= $rep['apellido_rep'] ?>"></div>
                                <div class="col-md-3">
                                    <label class="small fw-bold">Tipo</label>
                                    <select name="tipo_doc_rep" class="form-select form-select-sm">
                                        <option value="V" <?= $rep['tipo_doc_rep'] == 'V' ? 'selected' : '' ?>>V</option>
                                        <option value="E" <?= $rep['tipo_doc_rep'] == 'E' ? 'selected' : '' ?>>E</option>
                                    </select>
                                </div>
                                <div class="col-md-9"><label class="small fw-bold">Cédula</label><input type="number" name="cedula_rep" class="form-control form-control-sm" value="<?= $rep['cedula_rep'] ?>"></div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">Nacionalidad</label>
                                    <select name="id_nacionalidad_rep" class="form-select form-select-sm">
                                        <?php foreach ($nacionalidades as $n): ?>
                                            <option value="<?= $n['id_nacionalidad'] ?>" <?= $n['id_nacionalidad'] == $rep['id_nacionalidad_rep'] ? 'selected' : '' ?>><?= $n['nombre_nacionalidad'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6"><label class="small fw-bold">Fecha Nac.</label><input type="date" name="fecha_nac_rep" class="form-control form-control-sm" value="<?= $rep['fecha_nac_rep'] ?>"></div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>

        <div class="tab-pane fade" id="ubicacion">
            <fieldset id="fieldUbicacion" disabled>
                <div class="row g-2">
                    <div class="col-md-4"><label class="small fw-bold text-success">Teléfono</label><input type="text" name="tel_rep" class="form-control form-control-sm" value="<?= $rep['tel_rep'] ?>" form="formActualizarRep"></div>
                    <div class="col-md-5"><label class="small fw-bold text-success">Correo</label><input type="email" name="correo_rep" class="form-control form-control-sm" value="<?= $rep['correo_rep'] ?>" form="formActualizarRep"></div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-success">Parentesco</label>
                        <select name="parentesco_rep" class="form-select form-select-sm" form="formActualizarRep">
                            <?php foreach ($parentescos as $pt): ?>
                                <option value="<?= $pt['id_parentesco'] ?>" <?= $pt['id_parentesco'] == $rep['parentesco_rep'] ? 'selected' : '' ?>><?= $pt['nombre_parentesco'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Estado</label>
                        <select name="id_estado_rep" id="edit_estado_rep" class="form-select form-select-sm" form="formActualizarRep">
                            <?php foreach ($estados as $e): ?>
                                <option value="<?= $e['id_estado'] ?>" <?= $e['id_estado'] == $rep['id_estado_rep'] ? 'selected' : '' ?>><?= $e['nombre_estado'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Municipio</label>
                        <select name="id_municipio_rep" id="edit_municipio_rep" class="form-select form-select-sm" form="formActualizarRep">
                            <?php foreach ($municipios as $m): ?>
                                <option value="<?= $m['id_municipio'] ?>" <?= $m['id_municipio'] == $rep['id_municipio_rep'] ? 'selected' : '' ?>><?= $m['nombre_municipio'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Parroquia</label>
                        <select name="id_parroquia_rep" id="edit_parroquia_rep" class="form-select form-select-sm" form="formActualizarRep">
                            <?php foreach ($parroquias as $pq): ?>
                                <option value="<?= $pq['id_parroquia'] ?>" <?= $pq['id_parroquia'] == $rep['id_parroquia_rep'] ? 'selected' : '' ?>><?= $pq['nombre_parroquia'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12"><label class="small fw-bold">Dirección Detallada</label><textarea name="direccion_detalle_rep" class="form-control form-control-sm" rows="2" form="formActualizarRep"><?= $rep['direccion_detalle_rep'] ?></textarea></div>
                </div>
            </fieldset>
        </div>

        <div class="tab-pane fade" id="documentos">
            <fieldset id="fieldDocs" disabled>
                <div class="card bg-light border p-4 text-center">
                    <i class="bi bi-file-earmark-pdf text-danger display-6 mb-2"></i>
                    <label class="small fw-bold d-block mb-3">Cédula de Identidad Digitalizada</label>
                    <?php if (!empty($rep['pdf_cedula_rep'])): ?>
                        <div class="btn-group mb-3">
                            <a href="../../../uploads/documentos_reps/<?= $rep['pdf_cedula_rep'] ?>" target="_blank" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i> Ver Actual</a>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="pdf_cedula_rep" class="form-control form-control-sm mx-auto" style="max-width: 350px;" form="formActualizarRep">
                </div>
            </fieldset>
        </div>

        <div class="tab-pane fade" id="tabHijosPane">
            <div class="table-responsive">
                <table class="table table-sm table-hover border">
                    <thead class="table-success small text-center">
                        <tr><th>Estudiante</th><th>Grado/Sección</th><th>Acción</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hijos as $h): ?>
                            <tr class="align-middle small">
                                <td>
                                    <div class="fw-bold"><?= $h['nombre_es'] ?> <?= $h['apellido_es'] ?></div>
                                    <span class="badge bg-secondary"><?= $h['tipo_doc_es'] ?>-<?= $h['cedula_es'] ?: $h['cedula_escolar'] ?></span>
                                </td>
                                <td class="text-center"><?= $h['nombre_grado'] ?> - "<?= $h['nombre_seccion'] ?>"</td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-primary py-0" onclick="verDetalleEstudiante(<?= $h['id_estudiante'] ?>)"><i class="bi bi-search"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($hijos)): ?><tr><td colspan="3" class="text-center py-4 text-muted">No tiene alumnos registrados.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer bg-light py-2">
    <button type="button" class="btn btn-sm btn-secondary fw-bold" data-bs-dismiss="modal">SALIR</button>
    <button type="button" id="btnActualizarRep" class="btn btn-sm btn-success fw-bold disabled shadow"><i class="bi bi-save2 me-1"></i> GUARDAR CAMBIOS</button>
</div>

<script>
$(document).ready(function() {
    // 1. Switch de Edición
    $('#switchEditarRep').off('change').on('change', function() {
        const active = $(this).is(':checked');
        $('#fieldRep, #fieldUbicacion, #fieldDocs').prop('disabled', !active);
        $('#btnActualizarRep').toggleClass('disabled', !active);
    });

    // 2. Cascada de Ubicación (Rutas Corregidas)
    $('#edit_estado_rep').on('change', function() {
        const id = $(this).val();
        $.get('../../../modulos/controllers/ubicacion/get_municipios.php', { id_estado: id }, function(data) {
            $('#edit_municipio_rep').html(data);
            $('#edit_parroquia_rep').html('<option value="">Seleccione...</option>');
        });
    });

    $('#edit_municipio_rep').on('change', function() {
        const id = $(this).val();
        $.get('../../../modulos/controllers/ubicacion/get_parroquias.php', { id_municipio: id }, function(data) {
            $('#edit_parroquia_rep').html(data);
        });
    });

    // 3. Envío AJAX (Corregida sintaxis de doble $.ajax)
    $('#btnActualizarRep').off('click').on('click', function() {
        if ($(this).hasClass('disabled')) return;

        const formData = new FormData(document.getElementById('formActualizarRep'));
        const btn = $(this);
        const originalText = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

        $.ajax({
            url: '../../../modulos/controllers/representante/actualizar_representante.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === "success") {
                    alert("✅ " + response.message);
                    bootstrap.Modal.getInstance(document.getElementById('modalEditarRepresentante')).hide();
                    if (typeof tableRepresentantes !== 'undefined') tableRepresentantes.ajax.reload(null, false);
                    else location.reload();
                } else {
                    alert("❌ Error: " + response.message);
                }
            },
            error: function() { alert("❌ Error en el servidor."); },
            complete: function() { btn.prop('disabled', false).html(originalText); }
        });
    });
});

// Preview fuera del ready
function m_previewImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => $('#m_pre_foto').attr('src', e.target.result);
        reader.readAsDataURL(input.files[0]);
    }
}
</script>