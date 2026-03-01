<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/init.php'; // ← Centraliza sesión, conexión y funciones

// Validación de permiso institucional
if (!tienePermiso($pdo, 'planes_ver')) {
  registrarAcceso($pdo, $_SESSION['usuario']['id'] ?? null, 'planes_ver', 'denegado', 'Acceso sin permiso a tabla_planes');
  exit('⛔ Acceso denegado');
}
registrarAcceso($pdo, $_SESSION['usuario']['id'], 'planes_ver', 'permitido');

// Paginación
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$registros_por_pagina = 10;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Consulta principal
$sql = "
 SELECT 
  pe.id, 
  pe.codigo, 
  pe.descripcion, 
  pe.activo,
  pe.total_horasPlan, -- ← valor directo desde la tabla
  j.nombre,
  gac.numero AS gaceta,
  ne.nombre AS nivel_educativo
FROM plan_estudio pe
LEFT JOIN jornada_plan j ON pe.id = j.id
LEFT JOIN gaceta_oficial gac ON pe.id_gaceta = gac.id
LEFT JOIN nivel_educativo ne ON pe.id_nivelEducativo = ne.id_nivel
GROUP BY pe.id
ORDER BY pe.codigo ASC
LIMIT :limite OFFSET :offset;

";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limite', $registros_por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$planes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total de registros
$total_sql = "SELECT COUNT(*) FROM plan_estudio";
$total_registros = $pdo->query($total_sql)->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);
?>

<?php if ($planes): ?>
  <?php foreach ($planes as $row): ?>
    <tr>
      <td><?= htmlspecialchars($row['codigo']) ?></td>
      <td><?= htmlspecialchars($row['descripcion'] ?? '—') ?></td>
      <td><?= htmlspecialchars($row['nivel_educativo'] ?? '—') ?></td>
      <td><?= htmlspecialchars($row['nombre'] ?? '—') ?></td>
      <td><?= htmlspecialchars($row['gaceta'] ?? '—') ?></td>
      <td><?= htmlspecialchars($row['total_horasPlan'] ?? '0') ?> h/sem</td>
      <td>
        <span class="badge <?= !empty($row['activo']) ? 'bg-success' : 'bg-secondary' ?>">
          <?= !empty($row['activo']) ? 'Activo' : 'Inactivo' ?>
        </span>
      </td>
      <td>
        <div class="dropdown">
          <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Opciones
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="router.php?ruta=estructura_plan/ficha&codigo=<?= urlencode($row['codigo']) ?>"><i class="bi bi-eye"></i> Ver Ficha</a></li>
            <li><a class="dropdown-item" href="router.php?ruta=estructura_plan/editar_asignaturas&codigo=<?= urlencode($row['codigo']) ?>"><i class="bi bi-pencil-square"></i> Editar Asignaturas</a></li>
            <li><a class="dropdown-item" href="router.php?ruta=estructura_plan/editar_plan&codigo=<?= urlencode($row['codigo']) ?>"><i class="bi bi-tools"></i> Editar Plan</a></li>
            <li><a class="dropdown-item" href="router.php?ruta=estructura_plan/componentes&codigo=<?= urlencode($row['codigo']) ?>"><i class="bi bi-diagram-3"></i> Componentes Curriculares</a></li>
            <li><a class="dropdown-item" href="router.php?ruta=estructura_plan/historial&codigo=<?= urlencode($row['codigo']) ?>"><i class="bi bi-clock-history"></i> Historial de Cambios</a></li>
            <li><a class="dropdown-item text-success" href="router.php?ruta=planes/catalogo&plan_id=<?= urlencode($row['id']) ?>"><i class="bi bi-journal-plus"></i> Agregar Asignaturas</a></li>
            <li><a class="dropdown-item text-danger" href="router.php?ruta=estructura_plan/eliminar_plan&codigo=<?= urlencode($row['codigo']) ?>"><i class="bi bi-trash"></i> Eliminar Plan</a></li>
          </ul>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <tr>
    <td colspan="8" class="text-center text-muted">No se encontraron planes de estudio</td>
  </tr>
<?php endif; ?>

<!--PAGINADOR-->
<nav>
  <ul class="pagination justify-content-center pagination-sm mt-3">
    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
      <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
        <button class="page-link" onclick="cargarTablaPlanes(<?= $i ?>)"><?= $i ?></button>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
