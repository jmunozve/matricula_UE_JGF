<?php
/**
 * modulos/views/secciones/lista_secciones.php
 */
require_once dirname(__DIR__, 3) . '/includes/db.php';

try {
    $pdo = Conexion::abrir();

    // Filtramos directamente por PRIMARIA para optimizar la carga
    $sql = "SELECT 
                m.id_modalidad, m.nombre_modalidad,
                n.id_nivel, n.nombre_nivel,
                p.id_plan, p.descripcion AS nombre_plan, p.codigo_plan,
                g.id_grado, g.nombre_grado,
                s.id_seccion, s.letra, s.turno, s.capacidad_max,
                d.nombre AS doc_nombre, d.apellido AS doc_apellido,
                (SELECT COUNT(*) FROM estudiantes e WHERE e.id_seccion = s.id_seccion) as total_estudiantes,
                (SELECT COUNT(*) FROM estudiantes eM WHERE eM.id_seccion = s.id_seccion AND (eM.sexo_es = 'M' OR eM.sexo_es = '1')) as total_masculinos,
                (SELECT COUNT(*) FROM estudiantes eF WHERE eF.id_seccion = s.id_seccion AND (eF.sexo_es = 'F' OR eF.sexo_es = '2')) as total_femeninos
            FROM modalidades m
            INNER JOIN niveles_estudio n ON n.id_modalidad = m.id_modalidad
            INNER JOIN planes_estudio p ON p.id_nivel = n.id_nivel
            INNER JOIN grados g ON g.id_plan = p.id_plan
            LEFT JOIN secciones s ON s.id_grado = g.id_grado
            LEFT JOIN personal d ON s.id_docente = d.id_docente 
            WHERE n.nombre_nivel LIKE '%PRIMARIA%' 
            ORDER BY g.id_grado ASC, s.turno ASC, s.letra ASC";

    $datos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $jerarquia = [];
    $totales_global = ['m' => 0, 'f' => 0, 'l' => 0];
    $totales_nivel = [];
    $totales_grado = [];

    foreach ($datos as $row) {
        $jerarquia[$row['nombre_nivel']][$row['nombre_grado']][] = $row;

        if ($row['id_seccion']) {
            $m = (int)$row['total_masculinos'];
            $f = (int)$row['total_femeninos'];
            $lib = (int)$row['capacidad_max'] - ($m + $f);

            $totales_global['m'] += $m;
            $totales_global['f'] += $f;
            $totales_global['l'] += $lib;

            if (!isset($totales_nivel[$row['nombre_nivel']])) $totales_nivel[$row['nombre_nivel']] = ['m' => 0, 'f' => 0, 'l' => 0];
            $totales_nivel[$row['nombre_nivel']]['m'] += $m;
            $totales_nivel[$row['nombre_nivel']]['f'] += $f;
            $totales_nivel[$row['nombre_nivel']]['l'] += $lib;

            if (!isset($totales_grado[$row['nombre_grado']])) $totales_grado[$row['nombre_grado']] = ['m' => 0, 'f' => 0, 'l' => 0];
            $totales_grado[$row['nombre_grado']]['m'] += $m;
            $totales_grado[$row['nombre_grado']]['f'] += $f;
            $totales_grado[$row['nombre_grado']]['l'] += $lib;
        }
    }
} catch (Exception $e) {
    die("<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>");
}
?>

<style>
    .dot-cupo { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 2px; }
    .dot-masculino { background-color: #0d6efd; }
    .dot-femenino { background-color: #d63384; }
    .dot-vacio { background-color: #e9ecef; border: 1px solid #dee2e6; }
    .card-seccion { border-left: 4px solid #0d6efd !important; min-height: 200px; transition: all 0.2s; }
    .card-seccion:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important; }
    .grado-header-clickable { cursor: pointer; transition: background 0.2s; border-radius: 10px; }
    .grado-header-clickable:hover { background: #f8f9fa !important; }
    .text-pink { color: #d63384 !important; }
    .pill-total { font-size: 0.8rem; background: #f8f9fa; padding: 2px 10px; border-radius: 50px; border: 1px solid #eee; }
    .x-small { font-size: 0.75rem; }
</style>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-grid-3x3-gap me-2 text-primary"></i>DISPONIBILIDAD: PRIMARIA</h4>
        </div>
        <div class="d-flex gap-3 align-items-center bg-white p-2 rounded-3 shadow-sm border">
            <span class="fw-bold text-primary small"><i class="bi bi-gender-male"></i> <?= $totales_global['m'] ?></span>
            <span class="fw-bold text-pink small"><i class="bi bi-gender-female"></i> <?= $totales_global['f'] ?></span>
            <span class="fw-bold text-muted small"><i class="bi bi-dash-circle"></i> <?= $totales_global['l'] ?></span>
        </div>
    </div>

    <div class="accordion" id="accordionPrincipal">
        <?php 
        $idxNivel = 0;
        foreach ($jerarquia as $nivel => $grados): 
            $idxNivel++;
        ?>
            <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                <h2 class="accordion-header">
                    <button class="accordion-button fw-bold bg-white text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#nivel-<?= $idxNivel ?>">
                        <i class="bi bi-mortarboard-fill me-2"></i> <?= htmlspecialchars($nivel) ?>
                    </button>
                </h2>

                <div id="nivel-<?= $idxNivel ?>" class="accordion-collapse collapse show">
                    <div class="accordion-body bg-light px-3">
                        <div class="accordion accordion-flush" id="accGrados-<?= $idxNivel ?>">
                            <?php 
                            $idxGrado = 0;
                            foreach ($grados as $grado => $secciones): 
                                $idxGrado++;
                                $info_p = $secciones[0];
                                $tg = $totales_grado[$grado] ?? ['m' => 0, 'f' => 0, 'l' => 0];
                                $targetID = "collapseGrado-" . $idxNivel . "-" . $idxGrado;
                            ?>
                                <div class="accordion-item mb-2 border rounded-3 overflow-hidden shadow-sm">
                                    <div class="grado-header-clickable bg-white p-3 d-flex justify-content-between align-items-center" 
                                         data-bs-toggle="collapse" 
                                         data-bs-target="#<?= $targetID ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white p-2 rounded-2 me-3"><i class="bi bi-people-fill"></i></div>
                                            <div>
                                                <small class="text-muted d-block" style="font-size: 0.7rem;"><?= $info_p['codigo_plan'] ?></small>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($grado) ?></h6>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <div class="d-none d-md-flex gap-2 me-3 border-end pe-3">
                                                <span class="pill-total text-primary"><?= $tg['m'] ?> M</span>
                                                <span class="pill-total text-pink"><?= $tg['f'] ?> F</span>
                                                <span class="pill-total text-muted"><?= $tg['l'] ?> L</span>
                                            </div>
                                            <a href="/matricula/modulos/views/secciones/crear_seccion.php?id_grado=<?= $info_p['id_grado'] ?>" 
                                               class="btn btn-success btn-sm rounded-circle" 
                                               onclick="event.stopPropagation();">
                                                <i class="bi bi-plus-lg"></i>
                                            </a>
                                            <i class="bi bi-chevron-down ms-2 text-muted"></i>
                                        </div>
                                    </div>

                                    <div id="<?= $targetID ?>" class="accordion-collapse collapse" data-bs-parent="#accGrados-<?= $idxNivel ?>">
                                        <div class="accordion-body bg-white border-top">
                                            <div class="row g-3">
                                                <?php foreach ($secciones as $sec): if (!$sec['id_seccion']) continue; 
                                                    $m = (int)$sec['total_masculinos'];
                                                    $f = (int)$sec['total_femeninos'];
                                                    $total_sec = $m + $f;
                                                    $cap = (int)$sec['capacidad_max'];
                                                ?>
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="card card-seccion border-0 shadow-sm h-100">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex justify-content-between mb-2">
                                                                    <h6 class="fw-bold mb-0">Secc. "<?= $sec['letra'] ?>"</h6>
                                                                    <span class="badge <?= ($total_sec >= $cap) ? 'bg-danger' : 'bg-success' ?> rounded-pill">
                                                                        <?= $total_sec ?>/<?= $cap ?>
                                                                    </span>
                                                                </div>
                                                                
                                                                <div class="py-2 d-flex flex-wrap gap-1 border-top mb-2">
                                                                    <?php
                                                                    for ($i = 1; $i <= $cap; $i++) {
                                                                        if ($i <= $m) echo '<span class="dot-cupo dot-masculino"></span>';
                                                                        elseif ($i <= $total_sec) echo '<span class="dot-cupo dot-femenino"></span>';
                                                                        else echo '<span class="dot-cupo dot-vacio"></span>';
                                                                    }
                                                                    ?>
                                                                </div>

                                                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                                                    <span class="x-small text-muted"><?= $sec['doc_nombre'] ? ($sec['doc_nombre'][0].". ".$sec['doc_apellido']) : 'S/D' ?></span>
                                                                    <div class="btn-group">
                                                                        <button class="btn btn-sm btn-outline-primary border-0" title="Ver Detalles" onclick="verDetalleSeccion(<?= $sec['id_seccion'] ?>)">
                                                                            <i class="bi bi-eye-fill"></i>
                                                                        </button>
                                                                        <button class="btn btn-sm btn-outline-danger border-0" title="Eliminar Sección" onclick="eliminarSeccion(<?= $sec['id_seccion'] ?>)">
                                                                            <i class="bi bi-trash3-fill"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Asegúrate de tener estas funciones en tu archivo JS principal o aquí mismo
function eliminarSeccion(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción eliminará la sección. Solo podrás hacerlo si no tiene alumnos inscritos.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí llamarías a tu controlador de eliminación vía fetch o form
            window.location.href = `/matricula/modulos/controllers/secciones/eliminar_seccion.php?id=${id}`;
        }
    })
}
</script>