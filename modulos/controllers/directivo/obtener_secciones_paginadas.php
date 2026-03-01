<?php
// F:\xampp\htdocs\matricula\modulos\controllers\directivo\obtener_secciones_paginadas.php
require_once '../../../includes/db.php';
$pdo = Conexion::abrir();

$nivel = $_GET['nivel'] ?? '';
$pagina = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
$por_pagina = 10;
$inicio = ($pagina > 1) ? ($pagina * $por_pagina) - $por_pagina : 0;

$where = " WHERE s.estatus = 'Activo'";
$params = [];
if ($nivel !== '') {
    $where .= " AND n.id_nivel = :nivel";
    $params[':nivel'] = $nivel;
}

// 1. TOTALES GLOBALES DEL FILTRO (Para Cards y Gráfico)
$sql_global = "SELECT SUM(s.capacidad_max) as total_cap, COUNT(v.id_estudiante) as total_ins
               FROM secciones s
               JOIN grados g ON s.id_grado = g.id_grado 
               JOIN planes_estudio p ON g.id_plan = p.id_plan 
               JOIN niveles_estudio n ON p.id_nivel = n.id_nivel
               LEFT JOIN vista_estudiantes_completa v ON s.id_seccion = v.id_seccion
               $where";
$stmt_g = $pdo->prepare($sql_global);
$stmt_g->execute($params);
$globales = $stmt_g->fetch(PDO::FETCH_ASSOC);

// 2. PAGINACIÓN
$total_sql = "SELECT COUNT(*) FROM secciones s JOIN grados g ON s.id_grado = g.id_grado JOIN planes_estudio p ON g.id_plan = p.id_plan JOIN niveles_estudio n ON p.id_nivel = n.id_nivel $where";
$stmt_c = $pdo->prepare($total_sql);
$stmt_c->execute($params);
$total_reg = $stmt_c->fetchColumn();
$total_pag = ceil($total_reg / $por_pagina);

// 3. DATOS DE LA TABLA
$sql = "SELECT s.id_seccion, g.nombre_grado, n.nombre_nivel, s.letra, s.turno, s.capacidad_max,
        (SELECT COUNT(*) FROM vista_estudiantes_completa v WHERE v.id_seccion = s.id_seccion) as inscritos,
        (SELECT COUNT(*) FROM vista_estudiantes_completa v WHERE v.id_seccion = s.id_seccion AND v.sexo_es = '1') as masc,
        (SELECT COUNT(*) FROM vista_estudiantes_completa v WHERE v.id_seccion = s.id_seccion AND v.sexo_es = '2') as fem
        FROM secciones s
        JOIN grados g ON s.id_grado = g.id_grado
        JOIN planes_estudio p ON g.id_plan = p.id_plan
        JOIN niveles_estudio n ON p.id_nivel = n.id_nivel
        $where ORDER BY n.id_nivel, g.id_grado, s.letra LIMIT $inicio, $por_pagina";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode([
    'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    'paginas' => $total_pag,
    'pagina_actual' => $pagina,
    'total_reg' => $total_reg,
    'global_capacidad' => $globales['total_cap'] ?? 0,
    'global_inscritos' => $globales['total_ins'] ?? 0
]);