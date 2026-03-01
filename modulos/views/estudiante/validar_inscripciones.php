<?php
/**
 * Ubicación: F:\xampp\htdocs\matricula\modulos\views\estudiante\validar_inscripciones.php
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/header.php';

// Seguridad: Verificar sesión y rol
if (!isset($_SESSION['rol'])) {
    echo "<script>window.location.href='/matricula/modulos/views/login.php';</script>";
    exit;
}

$roles_permitidos = ['directora', 'coordinador', 'admin', 'superusuario'];
$rol_actual = strtolower(trim($_SESSION['rol'] ?? ''));

if (!in_array($rol_actual, $roles_permitidos)) {
    echo "<script>window.location.href='/matricula/modulos/views/dashboard.php?error=acceso_denegado';</script>";
    exit;
}

try {
    $pdo = Conexion::abrir();
    
    // Consulta optimizada para traer los nombres reales de grados y secciones
    $sql_pre = "SELECT 
                    e.*, 
                    s.letra, 
                    s.turno, 
                    g.nombre_grado
                FROM estudiantes e
                LEFT JOIN secciones s ON e.id_seccion = s.id_seccion
                LEFT JOIN grados g ON s.id_grado = g.id_grado
                WHERE e.estatus = 'Pre-inscrito' 
                ORDER BY e.pacto_multiple DESC, e.fecha_registro DESC";
                
    $preinscritos = $pdo->query($sql_pre)->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error en el sistema: " . $e->getMessage());
}

function calcularEdad($fecha) {
    if(!$fecha || $fecha == '0000-00-00') return 0;
    $nacimiento = new DateTime($fecha);
    $hoy = new DateTime();
    return $hoy->diff($nacimiento)->y;
}

// Rutas base para los archivos (Sincronizadas con tus carpetas reales)
$ruta_pdf = "/matricula/uploads/documentos_est/";
$ruta_foto = "/matricula/uploads/fotos_est/";
?>

<style>
    .swal2-popup { border-radius: 15px !important; font-size: 0.9rem !important; }
    .table-responsive { border-radius: 0 0 15px 15px; }
    .table thead th { font-size: 0.82rem; letter-spacing: 0.5px; text-transform: uppercase; background-color: #f8f9fa; }
    .bg-readonly { background-color: #f8f9fa !important; opacity: 1; border: 1px solid #dee2e6; font-size: 0.85rem; }
    .btn-doc { padding: 4px; font-size: 1.2rem; transition: transform 0.2s; display: inline-block; text-decoration: none; }
    .btn-doc:hover { transform: scale(1.2); }
    .doc-missing { opacity: 0.2; filter: grayscale(1); cursor: not-allowed; }
    .card-custom { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); border-radius: 1rem; }
    .foto-mini { width: 35px; height: 35px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; }
</style>

<div class="container-fluid px-4 mb-5">
    <div class="row mt-4 align-items-center">
        <div class="col-md-8">
            <a href="../academico/gestion_central.php" class="btn btn-sm btn-outline-secondary mb-2 rounded-pill shadow-sm">
                <i class="bi bi-arrow-left"></i> Volver al Panel
            </a>
            <h3 class="fw-bold text-dark mt-2">
                <i class="bi bi-shield-check text-success"></i> Validación de Expedientes
            </h3>
            <p class="text-muted small">Revise la documentación y formalice la inscripción definitiva.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="card bg-white border shadow-sm d-inline-block px-4 py-2 rounded-4">
                <span class="text-muted small fw-bold">PENDIENTES:</span>
                <span class="h4 mb-0 text-danger ms-2 fw-bold"><?= count($preinscritos) ?></span>
            </div>
        </div>
    </div>

    <div class="card card-custom mt-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="border-bottom">
                        <tr>
                            <th class="ps-4 py-3">Estudiante / Edad</th>
                            <th>Identificación</th>
                            <th>Registro</th>
                            <th>Expediente (Click para ver)</th>
                            <th style="width: 260px;">Ubicación Académica</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($preinscritos) > 0): ?>
                            <?php foreach ($preinscritos as $p): 
                                $edad = calcularEdad($p['fecha_nacimiento']);
                                $esFem = ($p['sexo_es'] == '2' || strtolower($p['sexo_es'] ?? '') == 'f');
                                $colorB = $esFem ? '#fdf2f8' : '#eff6ff'; 
                                $colorT = $esFem ? '#9d174d' : '#1e40af'; 
                                
                                $grado_n = $p['nombre_grado'] ?? 'S/N';
                                $letra_n = $p['letra'] ?? '';
                                $turno_n = $p['turno'] ?? '';

                                $seccion_texto = (!empty($letra_n)) 
                                    ? "$grado_n - Secc. $letra_n ($turno_n)" 
                                    : "No Asignada";
                            ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php if(!empty($p['foto_carnet'])): ?>
                                                <img src="<?= $ruta_foto . $p['foto_carnet'] ?>" class="foto-mini me-2">
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-dark small text-uppercase">
                                                    <?= htmlspecialchars(($p['apellido_es'] ?? '') . " " . ($p['nombre_es'] ?? '')) ?>
                                                </div>
                                                <span class="badge" style="background-color: <?= $colorB ?>; color: <?= $colorT ?>; font-size: 0.7rem;">
                                                    <?= $edad ?> AÑOS <?= ($p['pacto_multiple'] ?? 0) ? '• <i class="bi bi-people-fill"></i> GEMELO' : '' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-muted">CI: <?= $p['cedula_es'] ?: '<span class="text-danger">S/N</span>' ?></div>
                                        <div class="text-primary small fw-bold">CE: <?= $p['cedula_escolar'] ?? 'N/A' ?></div>
                                    </td>
                                    <td><small class="text-muted"><?= date('d/m/y', strtotime($p['fecha_registro'])) ?></small></td>
                                    
                                    <td>
                                        <div class="d-flex gap-2">
                                            <?php 
                                            // Mapeo corregido a los nombres de tu tabla
                                            $docs = [
                                                'doc_partida' => ['icon' => 'bi-file-earmark-pdf-fill', 'color' => 'text-danger',  'path' => $ruta_pdf,  'label' => 'Partida'],
                                                'doc_cedula'  => ['icon' => 'bi-person-vcard-fill',     'color' => 'text-info',    'path' => $ruta_pdf,  'label' => 'Cédula'],
                                                'foto_carnet' => ['icon' => 'bi-image-fill',            'color' => 'text-primary', 'path' => $ruta_foto, 'label' => 'Foto'],
                                                'doc_vacunas' => ['icon' => 'bi-droplet-fill',          'color' => 'text-warning', 'path' => $ruta_pdf,  'label' => 'Vacunas'],
                                                'doc_sano'    => ['icon' => 'bi-heart-pulse-fill',      'color' => 'text-success', 'path' => $ruta_pdf,  'label' => 'Niño Sano']
                                            ];

                                            foreach ($docs as $campo => $info):
                                                $archivo = $p[$campo] ?? '';
                                                $existe = !empty($archivo);
                                                $url = $existe ? $info['path'] . $archivo : '#';
                                            ?>
                                                <a href="<?= $url ?>" 
                                                   <?= $existe ? 'target="_blank"' : '' ?> 
                                                   class="btn-doc <?= $existe ? $info['color'] : 'doc-missing' ?>" 
                                                   title="<?= $info['label'] . ($existe ? ': Ver archivo' : ': No cargado') ?>">
                                                     <i class="bi <?= $info['icon'] ?>"></i>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-bookmark-star text-primary"></i></span>
                                            <input type="text" class="form-control form-control-sm bg-readonly fw-bold border-start-0" 
                                                   value="<?= htmlspecialchars($seccion_texto) ?>" readonly>
                                            <input type="hidden" id="sel_<?= $p['id_estudiante'] ?>" value="<?= $p['id_seccion'] ?? '0' ?>">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-success btn-sm rounded-pill px-3 shadow-sm fw-bold" 
                                                onclick="confirmarValidacion(<?= $p['id_estudiante'] ?>)"
                                                <?= (empty($p['id_seccion']) || empty($p['doc_partida'])) ? 'disabled title="Falta sección o partida"' : '' ?>>
                                            <i class="bi bi-check-lg"></i> Inscribir
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-check fs-1 d-block mb-2"></i>
                                    No hay solicitudes de inscripción pendientes.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="/matricula/public/assets/js/sweetalert2.all.min.js"></script>

<script>
function confirmarValidacion(idEst) {
    const idSec = document.getElementById('sel_' + idEst).value;
    
    if (!idSec || idSec === "0") {
        return Swal.fire({
            icon: 'warning',
            title: 'Falta Asignación',
            text: 'El estudiante no tiene una sección asignada para formalizar.',
            confirmButtonColor: '#0d6efd'
        });
    }

    Swal.fire({
        title: '¿Formalizar Inscripción?',
        html: `El estudiante pasará al listado oficial del año escolar en curso.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, formalizar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Se envía a validar_preinscritos.php que hará el UPDATE de estatus a 'Inscrito'
            window.location.href = `../../controllers/estudiante/validar_preinscritos.php?id=${idEst}&id_seccion=${idSec}`;
        }
    });
}
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/footer.php'; ?>