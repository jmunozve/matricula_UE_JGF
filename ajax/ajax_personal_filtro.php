<?php
require_once __DIR__ . '/../includes/db.php';
$conn = Conexion::abrir(); // institucional

$id_plantel = intval($_POST['id_plantel'] ?? 0);

// Inicializa condiciones y parámetros
$condiciones = [];
$parametros = [];
$tipos = '';

if ($id_plantel > 0) {
  $condiciones[] = 'id_plantel = ?';
  $parametros[] = $id_plantel;
  $tipos .= 'i';
}

// Filtrado por campos específicos
foreach ($_POST as $campo => $valor) {
  $valor = trim($valor);
  if ($valor === '') continue;

  if ($campo === 'documento_num') {
    $condiciones[] = 'documento LIKE ?';
    $parametros[] = '%' . $valor . '%';
    $tipos .= 's';
  } elseif (in_array($campo, ['nombres', 'apellidos', 'tipo_personal', 'periodo', 'estatus'])) {
    $condiciones[] = "$campo LIKE ?";
    $parametros[] = '%' . $valor . '%';
    $tipos .= 's';
  }
}

// Construye la consulta
$sql = "SELECT id, nombres, apellidos, tipo_documento, documento, tipo_personal, periodo, estatus
        FROM estructura_personal";

if ($condiciones) {
  $sql .= " WHERE " . implode(" AND ", $condiciones);
}

$sql .= " ORDER BY nombres ASC LIMIT 50";

// Ejecuta
$stmt = $conn->prepare($sql);
if ($parametros) {
  $stmt->bind_param($tipos, ...$parametros);
}
$stmt->execute();
$result = $stmt->get_result();

// Renderiza HTML de respuesta
if ($result->num_rows === 0): ?>
  <tr>
    <td colspan="7" class="text-center text-muted">🔍 No se encontraron coincidencias con los filtros.</td>
  </tr>
<?php else:
  while ($p = $result->fetch_assoc()): ?>
    <tr class="text-center">
      <td><?= htmlspecialchars($p['nombres']) ?></td>
      <td><?= htmlspecialchars($p['apellidos']) ?></td>
      <td><?= htmlspecialchars($p['tipo_documento'] . '-' . $p['documento']) ?></td>
      <td><?= htmlspecialchars($p['tipo_personal']) ?></td>
      <td><?= htmlspecialchars($p['periodo']) ?></td>
      <td>
        <span class="badge <?= $p['estatus'] === 'Activo' ? 'bg-success' : 'bg-secondary' ?>">
          <?= $p['estatus'] ?>
        </span>
      </td>
      <td class="acciones-columna">
        <a href="router.php?page=estructura_personal/registrar&id=<?= $p['id'] ?>" class="btn btn-sm btn-warning me-1">✏️</a>
        <?php if ($p['estatus'] === 'Activo'): ?>
          <button class="btn btn-sm btn-outline-secondary toggle-status" data-id="<?= $p['id'] ?>" data-status="Inactivo">🧊 Inactivar</button>
        <?php else: ?>
          <button class="btn btn-sm btn-outline-success toggle-status" data-id="<?= $p['id'] ?>" data-status="Activo">✅ Activar</button>
        <?php endif ?>
      </td>
    </tr>
  <?php endwhile;
endif;

$stmt->close();
