<?php
require_once '../includes/db.php';
header('Content-Type: application/json');


$res = $conn->query("SELECT id, nombre FROM seccion ORDER BY nombre");

$secciones = [];
while ($row = $res->fetch_assoc()) {
  $secciones[] = ['id' => $row['id'], 'nombre' => $row['nombre']];
}

echo json_encode($secciones);

?>