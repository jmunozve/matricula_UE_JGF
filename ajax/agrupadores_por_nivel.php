<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

$nivel = $_GET['nivel'] ?? '';

$nivelAgrupacion = [
  "1" => "grupo",
  "2" => "grupo",
  "3" => "grado",
  "4" => "año",
  "5" => "año",
  "6" => "periodo",
  "7" => "ambiente"
];

$tipo = $nivelAgrupacion[$nivel] ?? '';

if ($tipo === '') {
  echo json_encode([]);
  exit;
}

$stmt = $conn->prepare("SELECT id, nombre FROM agrupacion WHERE tipo = ? ORDER BY nombre ASC");
if (!$stmt) {
  echo json_encode([]);
  exit;
}

$stmt->bind_param("s", $tipo);
$stmt->execute();
$result = $stmt->get_result();

$agrupadores = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $agrupadores[] = [
      "id" => $row['id'],
      "nombre" => $row['nombre']
    ];
  }
}

echo json_encode($agrupadores);
?>
