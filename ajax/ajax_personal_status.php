<?php
require_once __DIR__ . '/../includes/db.php';

$id = intval($_POST['id'] ?? 0);
$estatus = $_POST['estatus'] ?? '';

if (!$id || !in_array($estatus, ['Activo', 'Inactivo'])) {
  echo json_encode(['success' => false]);
  exit;
}

$sql = "UPDATE estructura_personal SET estatus = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $estatus, $id);
$success = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $success]);
