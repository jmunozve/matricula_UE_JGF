<?php
/**
 * vistas_estudiantes/get_secciones.php - VERSIÓN OPTIMIZADA CON DESGLOSE INDEPENDIENTE
 */
error_reporting(E_ALL);
ini_set('display_errors', 0); 

$intentos = [
    dirname(__DIR__, 4) . '/includes/db.php',
    dirname(__DIR__, 3) . '/includes/db.php',
    "../../../includes/db.php"
];
$path_db = null;
foreach ($intentos as $ruta) {
    if (file_exists($ruta)) { $path_db = $ruta; break; }
}
if (!$path_db) die("Error de conexión");
require_once $path_db;

try {
    $pdo = Conexion::abrir();
    
    // Obtener niveles para el filtro superior
    $stmtNiv = $pdo->query("SELECT * FROM niveles_estudio ORDER BY nombre_nivel ASC");
    $niveles_db = $stmtNiv ? $stmtNiv->fetchAll(PDO::FETCH_ASSOC) : [];

    // Obtener las secciones con su conteo total (sin filtrar aún)
    $sqlSecciones = "SELECT s.id_seccion, s.letra, s.turno, g.nombre_grado, n.nombre_nivel,
                        (SELECT COUNT(*) FROM estudiante WHERE id_seccion = s.id_seccion) as total_estudiantes
                     FROM secciones s
                     JOIN grados g ON s.id_grado = g.id_grado
                     JOIN niveles_estudio n ON g.id_nivel = n.id_nivel
                     ORDER BY n.nombre_nivel, g.nombre_grado, s.letra";
    
    $stmtSec = $pdo->query($sqlSecciones);
    $secciones = $stmtSec ? $stmtSec->fetchAll(PDO::FETCH_ASSOC) : [];

} catch (Exception $e) {
    $niveles_db = [];
    $secciones = [];
    die("<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>");
}
?>

<div class="row mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-2 d-flex align-items-center bg-light rounded border">
                <div class="bg-primary text-white rounded p-2 me-3">
                    <i class="bi bi-filter-right fs-5"></i>
                </div>
                <div class="flex-grow-1">
                    <label class="fw-bold text-uppercase text-muted d-block mb-1" style="font-size: 0.6rem;">Nivel Educativo</label>
                    <select class="form-select form-select-sm border-0 bg-transparent fw-bold shadow-none" id="filtro-nivel" onchange="filtrarPorNivel()">
                        <option value="">TODOS LOS NIVELES</option>
                        <?php foreach($niveles_db as $niv): ?>
                            <option value="<?= htmlspecialchars($niv['nombre_nivel']) ?>"><?= htmlspecialchars($niv['nombre_nivel']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle border" id="tabla-secciones-maestra">
        <thead class="bg-dark text-white small">
            <tr>
                <th width="60" class="text-center">#</th>
                <th>Nivel de Estudio</th>
                <th>Grado / Año / Periodo</th>
                <th class="text-center">Turno</th>
                <th class="text-center">Sección</th>
                <th class="text-center">Matrícula</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($secciones)): ?>
                <?php foreach ($secciones as $sec): ?>
                    <tr class="fila-seccion" 
                        data-nivel="<?= htmlspecialchars($sec['nombre_nivel']) ?>"
                        style="cursor: pointer;" 
                        onclick="toggleEstudiantes(<?= $sec['id_seccion'] ?>)">
                        
                        <td class="text-center">
                            <i class="bi bi-plus-circle-fill text-primary shadow-sm" id="icon-<?= $sec['id_seccion'] ?>" style="font-size: 1.3rem;"></i>
                        </td>
                        <td class="fw-bold text-secondary"><?= $sec['nombre_nivel'] ?></td>
                        <td><?= $sec['nombre_grado'] ?></td>
                        <td class="text-center">
                            <?php 
                                $turno = strtoupper($sec['turno']);
                                $color_turno = ($turno == 'MAÑANA') ? 'text-warning' : (($turno == 'TARDE') ? 'text-info' : 'text-muted');
                            ?>
                            <span class="small fw-bold <?= $color_turno ?>">
                                 <?= $turno ?: 'N/P' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill px-3 shadow-sm"><?= $sec['letra'] ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                <?= $sec['total_estudiantes'] ?> Est.
                            </span>
                        </td>
                    </tr>
                    
                    <tr id="detalle-<?= $sec['id_seccion'] ?>" 
                        class="d-none fila-detalle bg-light shadow-inner" 
                        data-nivel="<?= htmlspecialchars($sec['nombre_nivel']) ?>">
                        <td colspan="6" class="p-3"> 
                            <div class="bg-white border rounded shadow-sm">
                                <div id="contenido-estudiantes-<?= $sec['id_seccion'] ?>">
                                    <div class="text-center py-4">
                                        <div class="spinner-grow spinner-grow-sm text-primary"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center py-4">No hay secciones registradas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
/**
 * Filtra las filas de las secciones y cierra los detalles abiertos
 */
function filtrarPorNivel() {
    const nivelSeleccionado = document.getElementById('filtro-nivel').value.toLowerCase();
    const filasPrincipales = document.querySelectorAll('.fila-seccion');
    const filasDetalle = document.querySelectorAll('.fila-detalle');

    filasPrincipales.forEach(f => {
        const nivelFila = f.getAttribute('data-nivel').toLowerCase();
        f.style.display = (nivelSeleccionado === "" || nivelFila === nivelSeleccionado) ? '' : 'none';
    });

    filasDetalle.forEach(fd => {
        // Al filtrar, cerramos todos los detalles por seguridad y reseteamos el icono
        fd.classList.add('d-none');
        const id = fd.id.replace('detalle-', '');
        const ico = document.getElementById(`icon-${id}`);
        if(ico) {
            ico.classList.replace('bi-dash-circle-fill', 'bi-plus-circle-fill');
            ico.classList.replace('text-danger', 'text-primary');
        }
        // Reseteamos el contenedor al spinner de carga
        document.getElementById(`contenido-estudiantes-${id}`).innerHTML = `
            <div class="text-center py-4"><div class="spinner-grow spinner-grow-sm text-primary"></div></div>
        `;
    });
}

/**
 * Controla la apertura/cierre del desglose de estudiantes
 */
function toggleEstudiantes(id) {
    const det = document.getElementById(`detalle-${id}`);
    const ico = document.getElementById(`icon-${id}`);

    if (det.classList.contains('d-none')) {
        det.classList.remove('d-none');
        ico.classList.replace('bi-plus-circle-fill', 'bi-dash-circle-fill');
        ico.classList.replace('text-primary', 'text-danger');
        
        // Llamada al nuevo archivo especializado
        cargarTablaAlumnos(id);
    } else {
        det.classList.add('d-none');
        ico.classList.replace('bi-dash-circle-fill', 'bi-plus-circle-fill');
        ico.classList.replace('text-danger', 'text-primary');
    }
}

/**
 * Carga el contenido desde el nuevo archivo especializado
 */
function cargarTablaAlumnos(id) {
    const nivelFiltro = document.getElementById('filtro-nivel').value;
    const contenedor = document.getElementById(`contenido-estudiantes-${id}`);
    
    // Invocamos el archivo especializado 'get_estudiantes_seccion.php'
    fetch(`vistas_estudiantes/get_estudiantes_seccion.php?id_seccion=${id}&nivel_filtro=${encodeURIComponent(nivelFiltro)}`)
        .then(r => r.text())
        .then(html => {
            contenedor.innerHTML = html;
        })
        .catch(err => {
            contenedor.innerHTML = '<div class="p-3 text-danger small">Error al cargar datos.</div>';
        });
}
</script>