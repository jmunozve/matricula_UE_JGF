<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/init.php';

if (!isset($_SESSION['usuario']['id'])) {
  mostrarMensaje('Sesión no iniciada', 'error');
  exit;
}

// Validación de acceso al módulo



function mostrarDocumento(string $cedula, string $tipo): array {
  return match ($tipo) {
    'CP' => ['provisional' => $cedula, 'normal' => '—'],
    'V', 'E', 'P' => ['provisional' => '—', 'normal' => $cedula],
    default => ['provisional' => '—', 'normal' => '—']
  };
}

$pagina_actual = isset($_POST['pagina']) ? max(1, intval($_POST['pagina'])) : 1;
$registros_por_pagina = 8;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$sql_base = "FROM estudiante e
LEFT JOIN representante r ON r.id = e.id_representante
LEFT JOIN matricula_estudiante m ON m.id_estudiante = e.id_estudiante
LEFT JOIN plantel p ON p.id = m.id_plantel
WHERE 1=1";

$params = [];
$where = [];

$campos = [
  'codigo_plantel_input' => 'p.codigo',
  'cedula_representante_input' => 'r.cedula',
  'cedula_estudiante_input' => 'e.cedula',
  'codigo_provisional_input' => 'e.cedula',
  'nombre_estudiante_input' => 'LOWER(e.nombres)',
  'apellido_estudiante_input' => 'LOWER(e.apellidos)'
];

foreach ($campos as $key => $campo_sql) {
  if (!empty($_POST[$key])) {
    $valor = trim($_POST[$key]);

    if (in_array($key, ['nombre_estudiante_input', 'apellido_estudiante_input']) && !preg_match('/^[\p{L}\s]+$/u', $valor)) {
      continue;
    }

    $where[] = "$campo_sql LIKE ?";
    $params[] = "%" . mb_strtolower($valor, 'UTF-8') . "%";
  }
}

$where_sql = $where ? " AND " . implode(" AND ", $where) : "";

$sql = "SELECT 
  e.id_estudiante, e.nombres, e.apellidos, e.cedula, e.tipo_documento,
  r.cedula AS cedula_rep, p.codigo AS codigo_plantel
  $sql_base $where_sql
  ORDER BY e.apellidos, e.nombres
  LIMIT ? OFFSET ?";

$params[] = $registros_por_pagina;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params as $i => $param) {
  $tipo = is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR;
  $stmt->bindValue($i + 1, $param, $tipo);
}

if (!$stmt->execute()) {
  echo "<tr><td colspan='7' class='text-danger text-center'>Error en la consulta</td></tr>";
  exit;
}

$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔢 Total sin LIMIT
$count_sql = "SELECT COUNT(*) $sql_base $where_sql";
$count_stmt = $pdo->prepare($count_sql);
foreach ($params as $i => $param) {
  if ($i < count($params) - 2) {
    $tipo = is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $count_stmt->bindValue($i + 1, $param, $tipo);
  }
}
$count_stmt->execute();
$total_registros = $count_stmt->fetchColumn();
$total_paginas = max(1, ceil($total_registros / $registros_por_pagina));

$html = '';

if ($resultados) {
  foreach ($resultados as $e) {
    $doc = mostrarDocumento($e['cedula'], $e['tipo_documento']);

    $html .= "<tr>
      <td>" . htmlspecialchars($e['codigo_plantel'] ?? '—') . "</td>
      <td>" . htmlspecialchars($e['cedula_rep'] ?? '—') . "</td>
      <td>" . htmlspecialchars($doc['provisional']) . "</td>
      <td>" . htmlspecialchars($doc['normal']) . "</td>
      <td>" . htmlspecialchars($e['nombres']) . "</td>
      <td>" . htmlspecialchars($e['apellidos']) . "</td>
      <td class='text-end'>
        <div class='d-inline-flex gap-2'>
          <a href='router.php?page=ficha_estudiante&id={$e['id_estudiante']}' class='btn btn-sm btn-outline-primary p-1'>📄</a>
          <a href='/matricula/router.php?ruta=estudiante/completar&id_estudiante={$e['id_estudiante']}' class='btn btn-sm btn-outline-success p-1'>🟢</a>
          <a href='router.php?page=egresar_estudiante&id={$e['id_estudiante']}' class='btn btn-sm btn-outline-danger p-1'>🚪</a>
        </div>
      </td>
    </tr>";
  }

  if ($total_paginas > 1) {
    $html .= "<tr id='paginacion-container'>
      <td colspan='7' class='p-3'>
        <div class='d-flex justify-content-center'>
          <nav aria-label='Paginación'>
            <ul class='pagination pagination-sm mb-0'>";
    for ($i = 1; $i <= $total_paginas; $i++) {
      $active = $i == $pagina_actual ? 'active' : '';
      $html .= "<li class='page-item $active'>
                  <a class='page-link pagination-link' href='#' data-pagina='$i'>$i</a>
                </li>";
    }
    $html .= "</ul></nav></div></td></tr>";
  }
} else {
  $html .= "<tr><td colspan='7' class='text-center text-muted py-4'>No se encontraron estudiantes</td></tr>";
}

echo $html;
