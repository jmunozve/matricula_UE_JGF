<?php
require_once '../includes/db.php';
header('Content-Type: application/json');


$nivelId = $_GET['nivel'] ?? null;
if (!$nivelId || !is_numeric($nivelId)) {
  echo json_encode([]);
  exit;
}

$stmt = $conn->prepare("SELECT id, nombre FROM agrupacion WHERE id_nivel_educativo = ? ORDER BY nombre");
$stmt->bind_param("i", $nivelId);
$stmt->execute();
$res = $stmt->get_result();

$agrupadores = [];
while ($row = $res->fetch_assoc()) {
  $agrupadores[] = ['id' => $row['id'], 'nombre' => $row['nombre']];
}

echo json_encode($agrupadores);

