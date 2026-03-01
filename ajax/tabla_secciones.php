<?php
// Devuelve solo filas HTML
header('Content-Type: text/html; charset=utf-8');

require_once '../includes/db.php';

$pagina = max(1, (int)($_POST['pagina'] ?? 1));
$id_institucion = (int)($_POST['id_institucion'] ?? 0);
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

if ($id_institucion <= 0) {
    echo "<tr><td colspan='9' class='text-center text-danger'>Error: ID de institución inválido.</td></tr>";
    exit;
}

// Filtros
$filtros = ['s.id_plantel = ?'];
$params = [$id_institucion];

if (!empty($_POST['grado'])) {
    $filtros[] = "s.id_agrupacion = ?";
    $params[] = $_POST['grado'];
}
if (!empty($_POST['seccion'])) {
    $filtros[] = "s.id_seccion3 = ?";
    $params[] = $_POST['seccion'];
}
if (!empty($_POST['turno'])) {
    $filtros[] = "s.id_turno = ?";
    $params[] = $_POST['turno'];
}
if (!empty($_POST['modalidad'])) {
    $filtros[] = "s.id_modalidad = ?";
    $params[] = $_POST['modalidad'];
}
if (!empty($_POST['nivel'])) {
    $filtros[] = "s.id_nivel_educativo = ?";
    $params[] = $_POST['nivel'];
}
if (!empty($_POST['plan_estudio'])) {
    $filtros[] = "s.id_plan_estudio = ?";
    $params[] = $_POST['plan_estudio'];
}
if (isset($_POST['activo'])) {
    $filtros[] = "s.activo = ?";
    $params[] = $_POST['activo'];
}

$where = "WHERE " . implode(" AND ", $filtros);

// Consulta principal
$sql = "SELECT 
            s.id,
            s.nombre AS seccion_nombre,
            agr.nombre AS grado,
            sec3.nombre AS nombre_seccion,
            t.nombre AS turno,
            m.nombre AS modalidad,
            ne.nombre AS nivel,
            pe.descripcion AS plan_estudio,
            s.cupos_maximos,
            s.activo
        FROM seccion_creada s
        LEFT JOIN agrupacion agr ON s.id_agrupacion = agr.id
        LEFT JOIN seccion3 sec3 ON s.id_seccion3 = sec3.id
        LEFT JOIN turno t ON s.id_turno = t.id
        LEFT JOIN modalidad m ON s.id_modalidad = m.id
        LEFT JOIN nivel_educativo ne ON s.id_nivel_educativo = ne.id
        LEFT JOIN plan_estudio pe ON s.id_plan_estudio = pe.id
        $where
        ORDER BY s.nombre
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $types = str_repeat('i', count($params)) . 'ii';
    $all_params = array_merge($params, [$por_pagina, $offset]);
    mysqli_stmt_bind_param($stmt, $types, ...$all_params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = false;
}

// Generar filas HTML
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = urlencode($row['id']);
        echo "<tr>
            <td><strong>" . htmlspecialchars($row['grado']) . "</strong></td>
            <td>" . htmlspecialchars($row['nombre_seccion'] ?: $row['seccion_nombre']) . "</td>
            <td>" . htmlspecialchars($row['turno']) . "</td>
            <td>" . htmlspecialchars($row['modalidad']) . "</td>
            <td>" . htmlspecialchars($row['nivel']) . "</td>
            <td>" . htmlspecialchars($row['plan_estudio']) . "</td>
            <td class='text-center'>" . (int)$row['cupos_maximos'] . "</td>
            <td>
                <span class='badge bg-" . ($row['activo'] ? 'success' : 'secondary') . "'>" . ($row['activo'] ? 'Activo' : 'Inactivo') . "</span>
            </td>
            <td>
                <div class='dropdown'>
                    <button class='btn btn-sm btn-outline-primary dropdown-toggle' type='button' data-bs-toggle='dropdown'>Opciones</button>
                    <ul class='dropdown-menu'>
                        <li>
                            <a class='dropdown-item' href='router.php?page=seccion/consultar&id=" . $id . "'>
                                <i class='bi bi-eye'></i> Consultar
                            </a>
                        </li>
                       <li>
                            <a class='dropdown-item' href='router.php?ruta=docentes/cargar_asignaturas&id=" . $id . "'>
                                <i class='bi bi-eye'></i> Cargar Asignaturas
                            </a>
                        </li>
                        <li>
                            <a class='dropdown-item' href='router.php?ruta=docentes/asignar_docente&id=" . $id . "'>
                                <i class='bi bi-person-lines-fill'></i> Asignar Docentes
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='9' class='text-center text-muted py-3'>No se encontraron secciones.</td></tr>";
}

mysqli_close($conn);
exit;
