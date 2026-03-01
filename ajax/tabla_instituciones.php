<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once __DIR__ . '/../includes/db.php';
$pdo = Conexion::abrir();

$rol = $_SESSION['usuario']['rol'] ?? 'usuario';
$id_plantel = $_SESSION['usuario']['id_plantel'] ?? null;


$pagina = max(1, (int)($_POST['pagina'] ?? 1));
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

$filtros = [];
$params = [];

if (!empty($_POST['codigo'])) {
    $filtros[] = "p.codigo LIKE ?";
    $params[] = '%' . $_POST['codigo'] . '%';
}
if (!empty($_POST['eponimo'])) {
    $filtros[] = "p.eponimo LIKE ?";
    $params[] = '%' . $_POST['eponimo'] . '%';
}
if (!empty($_POST['denominacion'])) {
    $filtros[] = "dp.nombre = ?";
    $params[] = $_POST['denominacion'];
}
if (!empty($_POST['dependencia'])) {
    $filtros[] = "dep.nombre = ?";
    $params[] = $_POST['dependencia'];
}
if (!empty($_POST['nivel'])) {
    $filtros[] = "ne.id_nivel = ?";
    $params[] = $_POST['nivel'];
}

if (!empty($_POST['modalidad'])) {
    $filtros[] = "m.id = ?";
    $params[] = $_POST['modalidad'];
}
if (isset($_POST['estatus'])) {
    $filtros[] = "p.activo = ?";
    $params[] = ($_POST['estatus'] === 'activo') ? 1 : 0;
}
if ($rol === 'admin') {
    // No se aplica filtro → acceso total
} elseif (!empty($id_plantel)) {
    $filtros[] = "p.id = ?";
    $params[] = intval($id_plantel);
}


$where = !empty($filtros) ? "WHERE " . implode(" AND ", $filtros) : "";

$sql = "SELECT 
            p.id,
            p.codigo,
            p.eponimo,
            dp.nombre AS denominacion,
            dep.nombre AS dependencia,
            GROUP_CONCAT(DISTINCT ne.nombre SEPARATOR ', ') AS niveles,
            GROUP_CONCAT(DISTINCT m.nombre SEPARATOR ', ') AS modalidades,
            p.activo
        FROM plantel p
        LEFT JOIN denominacion_plantel dp ON p.id_denominacion = dp.id
        LEFT JOIN dependencia_plantel dep ON p.id_dependencia = dep.id
        LEFT JOIN plantel_nivel pn ON p.id = pn.plantel_id
        LEFT JOIN nivel_educativo ne ON pn.id_nivel = ne.id_nivel
        LEFT JOIN plantel_modalidad pm ON p.id = pm.plantel_id
        LEFT JOIN modalidad m ON pm.id_modalidad = m.id
        $where
        GROUP BY p.id
        ORDER BY p.codigo
        LIMIT ? OFFSET ?";


$params[] = $por_pagina;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params as $i => $param) {
    $tipo = is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($i + 1, $param, $tipo);
}
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// === Estilos para dropdown ===
echo "<style>
.dropdown-menu {
  max-height: none !important;
  overflow: visible !important;
  position: absolute !important;
  z-index: 1050;
}
</style>";

// === Generar filas HTML ===
if ($result) {
    foreach ($result as $row) {
        echo "<tr>
            <td>" . htmlspecialchars($row['codigo']) . "</td>
            <td>" . htmlspecialchars($row['eponimo']) . "</td>
            <td>" . htmlspecialchars($row['denominacion'] ?: 'N/A') . "</td>
            <td>" . htmlspecialchars($row['dependencia'] ?: 'N/A') . "</td>
            <td>" . htmlspecialchars($row['niveles'] ?: 'N/A') . "</td>
            <td>" . htmlspecialchars($row['modalidades'] ?: 'N/A') . "</td>
            <td>
                <span class='badge bg-" . ($row['activo'] ? 'success' : 'secondary') . "'>
                    " . ($row['activo'] ? 'Activo' : 'Inactivo') . "
                </span>
            </td>
            <td>
                <div class='dropdown'>
                    <button class='btn btn-sm btn-outline-primary dropdown-toggle' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                        Opciones
                    </button>
                    <ul class='dropdown-menu'>
                        <li><a class='dropdown-item' href='router.php?page=institucion/consultar&id=" . urlencode($row['id']) . "'><i class='bi bi-eye'></i> Consultar</a></li>
                        <li><a class='dropdown-item' href='router.php?page=institucion/modificar&id=" . urlencode($row['id']) . "'><i class='bi bi-pencil'></i> Modificar</a></li>
                        <li><a class='dropdown-item' href='router.php?page=institucion/consultar&id=" . urlencode($row['id']) . "'><i class='bi bi-eye'></i> Niveles</a></li>
                        <li><a class='dropdown-item' href='router.php?page=institucion/modificar&id=" . urlencode($row['id']) . "'><i class='bi bi-pencil'></i> Planes</a></li>
                        <li><a class='dropdown-item' href='router.php?ruta=institucion/matricula/secciones&id=" . urlencode($row['id']) . "'><i class='bi bi-journal-text'></i> Matrículas</a></li>
                        <li><a class='dropdown-item' href='router.php?ruta=institucion/matricula/secciones&id=" . urlencode($row['id']) . "'><i class='bi bi-journal-text'></i> Estructura Plantel</a></li>
                    </ul>
                </div>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='8' class='text-center text-muted py-3'>No se encontraron instituciones.</td></tr>";
}

$conn = null;
exit;
