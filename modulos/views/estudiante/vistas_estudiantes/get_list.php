<?php
// F:\xampp\htdocs\matricula\modulos\views\estudiante\vistas_estudiantes\get_list.php
error_reporting(E_ALL);
ini_set('display_errors', 0); 

// Ruta absoluta para la DB
$path_db = dirname(__DIR__, 4) . '/includes/db.php';

if (file_exists($path_db)) {
    require_once $path_db;
} else {
    header("HTTP/1.1 500 Internal Server Error");
    die("Error: No se pudo localizar el archivo de base de datos.");
}

try {
    $pdo = Conexion::abrir();
    
    // Consulta para Primaria
    $sql = "SELECT * FROM vista_estudiantes_completa 
            WHERE UPPER(nombre_nivel) LIKE '%PRIMARIA%' 
            ORDER BY nombre_grado ASC, apellido_es ASC";
            
    $estudiantes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $grados = [];
    foreach ($estudiantes as $e) {
        $g = $e['nombre_grado'] ?? 'OTROS';
        $grados[$g][] = $e;
    }

} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    die("Error en get_list.php: " . $e->getMessage());
}
?>

<?php if (empty($grados)): ?>
    <div class="alert alert-info m-3">No hay estudiantes registrados en Primaria.</div>
<?php exit; endif; ?>

<div class="accordion accordion-flush px-3 pb-4" id="accordionPrimaria">
    <?php $i = 0; foreach ($grados as $grado => $lista): $id_acordeon = "grado_" . $i; ?>
        <div class="accordion-item shadow-sm mb-3 border rounded">
            <h2 class="accordion-header">
                <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?> fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $id_acordeon ?>">
                    <i class="bi bi-mortarboard-fill me-2 text-primary"></i> 
                    <?= htmlspecialchars($grado) ?> 
                    <span class="badge bg-primary ms-2"><?= count($lista) ?></span>
                </button>
            </h2>
            <div id="<?= $id_acordeon ?>" class="accordion-collapse collapse <?= $i == 0 ? 'show' : '' ?>" data-bs-parent="#accordionPrimaria">
                <div class="accordion-body bg-light-subtle">
                    <div class="row g-4">
                        <?php foreach ($lista as $e): 
                            $es_femenino = ($e['sexo_es'] == '2' || $e['sexo_es'] == 'F');
                            $color = $es_femenino ? '#d63384' : '#0d6efd';
                            $search_data = strtolower(($e['apellido_es']??"")." ".($e['nombre_es']??"")." ".($e['cedula_es']??""));
                            
                            $url_assets = "/matricula/public/assets/img/";
                            $foto_db = trim($e['foto_carnet'] ?? '');
                            $foto_final = (!empty($foto_db)) ? "/matricula/uploads/fotos_est/" . $foto_db : $url_assets . ($es_femenino ? "avatar_f.png" : "avatar_m.png");
                        ?>
                            <div class="col-xl-3 col-lg-4 col-md-6 item-estudiante" data-search="<?= $search_data ?>">
                                <div class="card card-estudiante shadow-sm border-top border-4 h-100" style="border-top-color: <?= $color ?> !important;">
                                    <?php if (($e['pacto_multiple'] ?? 0) >= 1): ?>
                                        <span class="badge-morocho">GEMELO</span>
                                    <?php endif; ?>

                                    <div class="card-body p-3 d-flex flex-column">
                                        <div class="d-flex align-items-center mb-3">
                                            <img src="<?= $foto_final ?>" class="foto-estudiante me-3 border shadow-sm" onerror="this.src='<?= $url_assets ?><?= $es_femenino ? 'avatar_f.png' : 'avatar_m.png' ?>';">
                                            <div class="overflow-hidden">
                                                <h6 class="mb-0 fw-bold text-dark text-truncate"><?= htmlspecialchars($e['apellido_es']) ?></h6>
                                                <p class="mb-1 text-muted small text-truncate"><?= htmlspecialchars($e['nombre_es']) ?></p>
                                                <span class="badge bg-light text-dark border-0" style="font-size: 0.7rem;">
                                                    <i class="bi bi-card-text me-1"></i><?= $e['cedula_escolar'] ?: $e['cedula_es'] ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="info-box mb-3">
                                            <div class="row g-0">
                                                <div class="col-6 border-end pe-2">
                                                    <div class="text-label">Grado / Sec</div>
                                                    <div class="text-value text-primary"><?= $e['nombre_grado'] ?> "<?= $e['nombre_seccion'] ?? '-' ?>"</div>
                                                </div>
                                                <div class="col-6 ps-2">
                                                    <div class="text-label">Turno</div>
                                                    <div class="text-value"><?= $e['turno'] ?? 'M' ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 mt-auto">
                                            <button class="btn btn-outline-primary btn-sm flex-grow-1" onclick="abrirModalEditar(<?= $e['id_estudiante'] ?>, '/matricula/')">
                                                <i class="bi bi-eye-fill"></i> Expediente
                                            </button>
                                            
                                            <button class="btn btn-outline-danger btn-sm" 
                                                    title="Retirar Estudiante" 
                                                    onclick="abrirModalRetiro(<?= $e['id_estudiante'] ?>, '/matricula/')">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php $i++; endforeach; ?>
</div>

<?php 
// Incluimos la modal de retiro desde la ruta absoluta que indicaste
$path_modal_retiro = dirname(__DIR__, 4) . '/includes/modal_retiro.php';
if (file_exists($path_modal_retiro)) {
    include_once $path_modal_retiro;
}
?>