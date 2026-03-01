<?php
/**
 * vistas_estudiantes/get_tabla.php
 * Versión optimizada: Solo Primaria
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Localización de db.php
$intentos = [
    dirname(__DIR__, 4) . '/includes/db.php',
    dirname(__DIR__, 3) . '/includes/db.php',
    "../../../includes/db.php"
];
$path_db = null;
foreach ($intentos as $ruta) {
    if (file_exists($ruta)) {
        $path_db = $ruta;
        break;
    }
}
if (!$path_db) die("Error de conexión");
require_once $path_db;

try {
    $pdo = Conexion::abrir();

    // --- LÓGICA DE PAGINACIÓN ---
    $por_pagina = 10;
    $pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    if ($pagina < 1) $pagina = 1;
    $offset = ($pagina - 1) * $por_pagina;

    // 1. Contar total SOLO de Primaria
    // Asumimos que la columna en la vista se llama 'nombre_nivel' o similar
    $sql_count = "SELECT COUNT(*) FROM vista_estudiantes_completa WHERE nombre_nivel LIKE '%Primaria%'";
    $total_registros = $pdo->query($sql_count)->fetchColumn();
    $total_paginas = ceil($total_registros / $por_pagina);

    // 2. Consulta filtrada por Primaria
    $sql = "SELECT * FROM vista_estudiantes_completa 
            WHERE nombre_nivel LIKE '%Primaria%'
            ORDER BY id_estudiante DESC 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("<div class='alert alert-danger m-3'>Error SQL: " . $e->getMessage() . "</div>");
}
?>

<style>
    .tabla-estudiantes-custom thead th {
        background-color: #f8f9fa;
        color: #6c757d;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding: 15px 10px;
        border-bottom: 2px solid #dee2e6;
    }
    .fila-datos:hover { background-color: #f8fbff !important; }
    .search-wrapper { background: #fff; padding: 20px; border-bottom: 1px solid #eee; }
</style>

<div class="search-wrapper">
    <div class="row align-items-center">
        <div class="col-md-6">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-primary"></i>
                </span>
                <input type="text" id="inputBusquedaInterna" class="form-control border-start-0 ps-0"
                    placeholder="Filtrar por nombre, cédula o grado..." oninput="ejecutarFiltroTabla()">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <span class="badge bg-primary-subtle text-primary border px-3 py-2">
                Nivel: Primaria
            </span>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 tabla-estudiantes-custom" id="tablaEstudiantes">
        <thead>
            <tr>
                <th width="40" class="text-center"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                <th>Cédula Escolar</th>
                <th>Identificación</th>
                <th>Estudiante</th>
                <th>Grado / Sección</th>
                <th>Estatus</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody class="small">
            <?php foreach ($estudiantes as $est):
                $cedula_full = ($est['cedula_es']) ? $est['tipo_doc_es'] . "-" . $est['cedula_es'] : 'N/A';
                
                // Atributo para búsqueda rápida en el cliente
                $search_data = strtolower($est['nombre_es'].' '.$est['apellido_es'].' '.$est['cedula_escolar'].' '.$est['nombre_grado']);
            ?>
                <tr class="fila-datos" data-search="<?= htmlspecialchars($search_data) ?>">
                    <td class="text-center"><input type="checkbox" class="form-check-input"></td>
                    <td>
                        <span class="badge bg-light text-secondary border">
                            <?= $est['cedula_escolar'] ?: 'S/A' ?>
                        </span>
                    </td>
                    <td><span class="fw-bold"><?= $cedula_full ?></span></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="text-uppercase"><?= htmlspecialchars($est['nombre_es'] . " " . $est['apellido_es']) ?></span>
                            <?php if (isset($est['pacto_multiple']) && $est['pacto_multiple'] >= 1): ?>
                                <span class="ms-2 badge rounded-pill bg-info text-white" style="font-size: 0.6rem;">GEMELO</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="small">
                            <?= $est['nombre_grado'] ?: 'S/G' ?><br>
                            <span class="text-primary fw-bold">Secc: <?= $est['nombre_seccion'] ?: '-' ?></span>
                        </div>
                    </td>
                    <td>
                        <?php
                        $estatus = strtolower($est['estatus'] ?? 'incompleto');
                        $color = match ($estatus) {
                            'inscrito' => 'bg-success',
                            'retirado' => 'bg-danger',
                            default => 'bg-warning text-dark'
                        };
                        ?>
                        <span class="badge rounded-pill <?= $color ?>"><?= strtoupper($estatus) ?></span>
                    </td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-light border" onclick="abrirModalEditar(<?= $est['id_estudiante'] ?>, '../../..')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-light border text-danger" onclick="window.abrirModalRetiro(<?= $est['id_estudiante'] ?>, '../../../')">
                                <i class="bi bi-person-x"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center p-3 border-top bg-light">
    <div class="small text-muted">
        Primaria: <?= $total_registros ?> registros encontrados.
    </div>
    <nav>
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="javascript:void(0)" onclick="filtrarContenido(<?= $pagina - 1 ?>)">Ant</a>
            </li>
            <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="javascript:void(0)" onclick="filtrarContenido(<?= $i ?>)"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($pagina >= $total_paginas) ? 'disabled' : '' ?>">
                <a class="page-link" href="javascript:void(0)" onclick="filtrarContenido(<?= $pagina + 1 ?>)">Sig</a>
            </li>
        </ul>
    </nav>
</div>

<script>
    // Filtro rápido sin recargar
    function ejecutarFiltroTabla() {
        const busqueda = document.getElementById('inputBusquedaInterna').value.toLowerCase();
        const filas = document.querySelectorAll('.fila-datos');
        
        filas.forEach(f => {
            const texto = f.getAttribute('data-search');
            f.style.display = texto.includes(busqueda) ? '' : 'none';
        });
    }
</script>