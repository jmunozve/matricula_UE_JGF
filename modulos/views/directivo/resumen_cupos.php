<?php
// F:\xampp\htdocs\matricula\modulos\views\directivo\resumen_cupos.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$_SESSION['ruta_activa'] = 'directivo/resumen_cupos';

require_once dirname(__DIR__, 3) . '/includes/header.php';
require_once dirname(__DIR__, 3) . '/includes/db.php';

$pdo = Conexion::abrir();

/** * 1. OBTENER RESUMEN POR TURNOS (En lugar de niveles)
 * Ya que todo es Primaria, es más útil ver cómo se divide la mañana y la tarde.
 */
$sql_resumen_turnos = "SELECT 
    s.turno, 
    COUNT(DISTINCT s.id_seccion) as total_secciones,
    SUM(CASE WHEN v.sexo_es = '1' THEN 1 ELSE 0 END) as masc,
    SUM(CASE WHEN v.sexo_es = '2' THEN 1 ELSE 0 END) as fem,
    COUNT(v.id_estudiante) as total_est
    FROM secciones s
    INNER JOIN grados g ON s.id_grado = g.id_grado
    INNER JOIN planes_estudio p ON g.id_plan = p.id_plan
    INNER JOIN niveles_estudio n ON p.id_nivel = n.id_nivel
    LEFT JOIN vista_estudiantes_completa v ON s.id_seccion = v.id_seccion
    WHERE n.nombre_nivel LIKE '%PRIMARIA%' AND s.estatus = 'Activo'
    GROUP BY s.turno";
$resumen_turnos = $pdo->query($sql_resumen_turnos)->fetchAll(PDO::FETCH_ASSOC);

$total_m = 0; $total_f = 0; $total_g = 0;
foreach($resumen_turnos as $r) {
    $total_m += $r['masc'];
    $total_f += $r['fem'];
    $total_g += $r['total_est'];
}
?>

<style>
    .card-stat { border: none; border-radius: 15px; transition: transform 0.3s ease; }
    .table-custom { border-radius: 15px; overflow: hidden; background: white; border: none !important; }
    .monitor-container { padding: 20px; background-color: #f4f7f6; }
    .mini-dashboard { border: none; border-radius: 15px; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .turno-row { border-bottom: 1px solid #f0f0f0; padding: 12px 0; }
    .turno-row:last-child { border-bottom: none; }
    .sex-m { color: #0d6efd; font-weight: bold; }
    .sex-f { color: #d63384; font-weight: bold; }
    .badge-turno { font-size: 0.7rem; padding: 4px 8px; }
</style>

<div class="container-fluid monitor-container">
    <div class="row align-items-center mb-4">
        <div class="col-md-12">
            <h4 class="fw-bold mb-0 text-dark">
                <i class="bi bi-speedometer2 me-2 text-primary"></i>Monitor de Cupos: Educación Primaria
            </h4>
            <p class="text-muted small mb-0">U.E.D. José Gil Fortoul | Resumen de Matrícula Actual</p>
        </div>
    </div>

    <div class="row g-3 mb-4 text-white text-center">
        <div class="col-md-4">
            <div class="card card-stat shadow-sm bg-primary p-3">
                <small class="text-uppercase opacity-75 fw-bold">Capacidad Planta</small>
                <h2 class="mb-0 fw-bold" id="card-total-capacidad">0</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat shadow-sm bg-success p-3">
                <small class="text-uppercase opacity-75 fw-bold">Estudiantes Inscritos</small>
                <h2 class="mb-0 fw-bold" id="card-total-inscritos">0</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat shadow-sm bg-info p-3">
                <small class="text-uppercase opacity-75 fw-bold">Vacantes Disponibles</small>
                <h2 class="mb-0 fw-bold" id="card-total-vacantes">0</h2>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h5 class="fw-bold mb-3 text-center">Ocupación de Plantel</h5>
                <div style="height: 250px;"><canvas id="chartDona"></canvas></div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card mini-dashboard p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-primary">Distribución por Turnos</h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3">Primaria</span>
                </div>
                
                <div class="resumen-turnos-body">
                    <?php foreach($resumen_turnos as $rn): ?>
                        <div class="turno-row d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-dark d-block text-uppercase"><?= $rn['turno'] ?></span>
                                <small class="text-muted"><?= $rn['total_secciones'] ?> Secciones activas</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark border me-2">
                                    <span class="sex-m">M: <?= $rn['masc'] ?></span> | <span class="sex-f">F: <?= $rn['fem'] ?></span>
                                </span>
                                <span class="fw-bold fs-5 text-dark"><?= $rn['total_est'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="p-3 bg-light rounded-3 mt-auto d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-muted small text-uppercase">Total General:</span>
                        <div class="text-end">
                            <span class="me-3"><i class="bi bi-gender-male text-primary"></i> <?= $total_m ?></span>
                            <span class="me-3"><i class="bi bi-gender-female text-pink"></i> <?= $total_f ?></span>
                            <span class="fw-bold fs-4 text-primary"><?= $total_g ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm table-custom">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="fw-bold mb-0">Detalle por Grado y Sección</h5>
            <span class="badge bg-dark rounded-pill px-3" id="contador-secciones">0 registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="bg-light text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4 text-start">Grado</th>
                        <th>Sección</th>
                        <th>Turno</th>
                        <th>Masc</th>
                        <th>Fem</th>
                        <th>Ocupación</th>
                        <th>Disponibilidad</th>
                        <th class="pe-4">Acción</th>
                    </tr>
                </thead>
                <tbody id="body-secciones"></tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3"><nav id="paginacion-secciones"></nav></div>
    </div>
</div>

<?php 
include_once "../../../includes/modal_detalle_seccion.php"; 
include_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/modales_estudiante.php';
require_once dirname(__DIR__, 3) . '/includes/footer.php'; 
?>

<script src="/matricula/public/assets/js/chart.min.js"></script>
<script>
let miChartDona;

// Ya no necesitamos el ID del nivel porque el controlador debería filtrar por Primaria internamente
async function cargarDashboardCompleto(pagina = 1) {
    const tbody = document.getElementById('body-secciones');
    tbody.innerHTML = '<tr><td colspan="8" class="py-5 text-center"><div class="spinner-border text-primary"></div></td></tr>';

    try {
        // Quitamos el parámetro de nivel de la URL o lo enviamos fijo si el controlador lo requiere
        const res = await fetch(`../../controllers/directivo/obtener_secciones_paginadas.php?pag=${pagina}`);
        const result = await res.json();
        tbody.innerHTML = '';
        
        if(result.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="py-5 text-muted">No hay registros en Primaria</td></tr>';
        } else {
            result.data.forEach(s => {
                const ins = parseInt(s.inscritos);
                const cap = parseInt(s.capacidad_max);
                const porc = cap > 0 ? (ins/cap)*100 : 0;
                const colorB = porc >= 100 ? "bg-danger" : (porc > 85 ? "bg-warning" : "bg-success");
                
                tbody.innerHTML += `<tr>
                    <td class="ps-4 text-start fw-bold">${s.nombre_grado}</td>
                    <td><span class="badge bg-white text-dark border rounded-pill px-3">${s.letra}</span></td>
                    <td class="small text-muted text-uppercase">${s.turno}</td>
                    <td class="sex-m">${s.masc}</td><td class="sex-f">${s.fem}</td>
                    <td><strong>${ins}</strong> <small class="text-muted">/ ${cap}</small></td>
                    <td style="width: 140px;">
                        <div class="progress" style="height: 6px; border-radius: 10px;">
                            <div class="progress-bar ${colorB}" style="width: ${porc}%"></div>
                        </div>
                        <small class="text-muted" style="font-size: 0.6rem;">${cap-ins} cupos libres</small>
                    </td>
                    <td><button class="btn btn-sm btn-primary rounded-pill px-3" onclick="abrirDetalleSeccion(${s.id_seccion})"><i class="bi bi-eye"></i></button></td>
                </tr>`;
            });
        }
        
        // Actualizar los cuadros de texto
        document.getElementById('card-total-capacidad').innerText = result.global_capacidad;
        document.getElementById('card-total-inscritos').innerText = result.global_inscritos;
        document.getElementById('card-total-vacantes').innerText = (result.global_capacidad - result.global_inscritos);
        document.getElementById('contador-secciones').innerText = `${result.total_reg} Secciones`;

        if (miChartDona) {
            const vacantes = result.global_capacidad - result.global_inscritos;
            miChartDona.data.datasets[0].data = [result.global_inscritos, vacantes > 0 ? vacantes : 0];
            miChartDona.update();
        }
        renderizarPaginacion(result.paginas, result.pagina_actual);
    } catch (e) { 
        tbody.innerHTML = '<tr><td colspan="8" class="text-danger py-4">Error de conexión con el servidor</td></tr>'; 
    }
}

function renderizarPaginacion(total, actual) {
    const nav = document.getElementById('paginacion-secciones');
    let html = '<ul class="pagination pagination-sm justify-content-center mb-0">';
    for (let i = 1; i <= total; i++) {
        html += `<li class="page-item ${i === actual ? 'active' : ''}"><a class="page-link mx-1 rounded-circle border-0" href="javascript:void(0)" onclick="cargarDashboardCompleto(${i})">${i}</a></li>`;
    }
    nav.innerHTML = html + '</ul>';
}

document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('chartDona').getContext('2d');
    miChartDona = new Chart(ctx, {
        type: 'doughnut',
        data: { 
            labels: ['Ocupado', 'Disponible'], 
            datasets: [{ 
                data: [0, 100], 
                backgroundColor: ['#0d6efd', '#f0f2f5'], 
                borderWidth: 0, 
                cutout: '80%' 
            }] 
        },
        options: { 
            maintainAspectRatio: false, 
            plugins: { legend: { display: true, position: 'bottom' } } 
        }
    });
    cargarDashboardCompleto(1);
});

function abrirDetalleSeccion(idSeccion) {
    const modalElement = document.getElementById('modalDetalleSeccion');
    let modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    document.getElementById('spinnerCarga').style.display = 'block';
    document.getElementById('datosSeccion').innerHTML = '';
    modal.show();
    fetch(`../../controllers/secciones/obtener_detalle_seccion.php?id=${idSeccion}&modo=lectura`)
        .then(res => res.text()).then(html => {
            document.getElementById('spinnerCarga').style.display = 'none';
            document.getElementById('datosSeccion').innerHTML = html;
        });
}
</script>