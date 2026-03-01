<?php
require_once '../includes/db.php';

$cedula = $_POST['cedula'];

$sql = "SELECT * FROM representantes WHERE cedula = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cedula);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
  $rep = $res->fetch_assoc();
  echo json_encode([
    "existe" => true,
    "datos" => $rep
  ]);
} else {
  echo json_encode(["existe" => false]);
}
