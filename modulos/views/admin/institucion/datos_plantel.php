<?php
// 1. Definición de la raíz del proyecto para PHP
$root = $_SERVER['DOCUMENT_ROOT'] . '/matricula';

// 2. Inclusiones con ruta física absoluta
require_once $root . '/includes/db.php';
require_once $root . '/includes/header.php';

// Iniciamos sesión si no está activa
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

$pdo = Conexion::abrir();

// 3. Verificación de Seguridad "A prueba de balas"
// Limpiamos el rol: minúsculas, sin espacios y aseguramos que sea string
$rol_session = isset($_SESSION['rol']) ? (string)$_SESSION['rol'] : '';
$rol_actual = strtolower(trim($rol_session));

// Variables de control de acceso
$es_super = (strpos($rol_actual, 'super') !== false);
$es_admin = (strpos($rol_actual, 'admin') !== false);

// Si no es ninguno de los dos, fuera del sistema
if (!$es_super && !$es_admin) {
    header("Location: /matricula/modulos/views/login.php?error=acceso_denegado");
    exit();
}

$planteles = $pdo->query("SELECT * FROM planteles ORDER BY id_plantel DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-bank2 me-2"></i>Institución Educativa</h3>
                <p class="mb-0 opacity-75">Gestión de identidad visual y datos oficiales del plantel.</p>
                <?php if($es_super): ?>
                    <span class="badge bg-light text-primary mt-2">Modo: Superusuario (Acceso Total)</span>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2">
                <a href="/matricula/modulos/views/admin/usuarios/configuracion.php" class="btn btn-outline-light rounded-pill px-3 fw-bold shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Regresar
                </a>
                
                <?php if ($es_super): ?>
                <button class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" onclick="nuevoPlantel()">
                    <i class="bi bi-plus-circle-fill me-2"></i>Registrar Sede
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaPrincipal">
                <thead class="table-light text-muted small">
                    <tr>
                        <th class="ps-4" width="80">LOGO</th>
                        <th>NOMBRE INSTITUCIÓN</th>
                        <th>CONTACTO Y DEA</th>
                        <th class="text-center">PERIODO</th>
                        <th class="text-end pe-4">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($planteles as $p): 
                        $logoName = !empty($p['logo_plantel']) ? $p['logo_plantel'] : 'logo.png';
                        $logoUrl = "/matricula/uploads/institucion/" . $logoName;
                    ?>
                    <tr>
                        <td class="ps-4">
                            <img src="<?= $logoUrl ?>?t=<?= time() ?>" 
                                 class="shadow-sm rounded border" 
                                 style="width: 55px; height: 55px; object-fit: contain; background: white;" 
                                 onerror="this.src='/matricula/uploads/institucion/logo.png'">
                        </td>
                        <td>
                            <div class="fw-bold text-dark text-uppercase"><?= htmlspecialchars($p['nombre_plantel']) ?></div>
                            <div class="text-muted x-small"><?= htmlspecialchars($p['direccion_plantel']) ?></div>
                        </td>
                        <td>
                            <div class="small"><span class="text-muted">DEA:</span> <?= htmlspecialchars($p['codigo_dea']) ?></div>
                            <div class="x-small text-muted italic"><?= htmlspecialchars($p['correo_plantel']) ?></div>
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill bg-soft-primary text-primary border border-primary px-3"><?= htmlspecialchars($p['periodo_escolar']) ?></span>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-info rounded-circle" title="Editar Información" onclick='editarPlantel(<?= json_encode($p) ?>)'>
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPlantel" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-sliders me-2"></i>Gestión de Plantel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPlantel" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_plantel" id="id_plantel">
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <div class="bg-light p-3 rounded-3 border mb-3">
                                <label class="form-label d-block fw-bold small text-muted">LOGO INSTITUCIONAL</label>
                                <img id="previewLogo" src="/matricula/uploads/institucion/logo.png" class="img-fluid rounded mb-3 shadow-sm border" style="max-height: 160px; background: white;">
                                <input type="file" name="logo_plantel" id="logo_input" class="form-control form-control-sm" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Nombre de la Institución</label>
                                    <input type="text" name="nombre_plantel" id="nombre_plantel" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Código DEA</label>
                                    <input type="text" name="codigo_dea" id="codigo_dea" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Periodo Escolar</label>
                                    <input type="text" name="periodo_escolar" id="periodo_escolar" class="form-control" placeholder="2025-2026">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Teléfono de Contacto</label>
                                    <input type="text" name="telefono_plantel" id="telefono_plantel" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Correo Electrónico</label>
                                    <input type="email" name="correo_plantel" id="correo_plantel" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small">Dirección Fiscal</label>
                                    <textarea name="direccion_plantel" id="direccion_plantel" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold shadow">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once $root . '/includes/footer.php'; ?>

<script>
// Funciones JavaScript igual que antes, pero operativas
function nuevoPlantel() {
    $('#formPlantel')[0].reset();
    $('#id_plantel').val('');
    $("#previewLogo").attr("src", "/matricula/uploads/institucion/logo.png");
    $('#modalPlantel').modal('show');
}

function editarPlantel(p) {
    $("#id_plantel").val(p.id_plantel);
    $("#nombre_plantel").val(p.nombre_plantel);
    $("#codigo_dea").val(p.codigo_dea);
    $("#periodo_escolar").val(p.periodo_escolar);
    $("#telefono_plantel").val(p.telefono_plantel);
    $("#correo_plantel").val(p.correo_plantel);
    $("#direccion_plantel").val(p.direccion_plantel);
    
    let logoName = p.logo_plantel ? p.logo_plantel : "logo.png";
    let logoRuta = "/matricula/uploads/institucion/" + logoName + "?t=" + new Date().getTime();
    
    $("#previewLogo").attr("src", logoRuta);
    $("#modalPlantel").modal("show");
}

$(document).ready(function() {
    // Vista previa de imagen
    $("#logo_input").on("change", function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => { $("#previewLogo").attr("src", e.target.result); }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Envío del formulario por AJAX
    $("#formPlantel").on("submit", function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        
        Swal.fire({ 
            title: 'Guardando datos...', 
            allowOutsideClick: false, 
            didOpen: () => { Swal.showLoading(); } 
        });

        $.ajax({
            url: '/matricula/modulos/controllers/admin/institucion/guardar_institucion.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                try {
                    let data = (typeof res === 'object') ? res : JSON.parse(res);
                    if(data.status === 'success') {
                        Swal.fire('¡Listo!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                } catch(e) {
                    Swal.fire('Error', 'Error en el servidor', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo procesar la solicitud', 'error');
            }
        });
    });
});
</script>