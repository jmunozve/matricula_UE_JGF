<?php
require_once '../includes/db.php';

$cedula = $_POST['cedula'];

$sql = "SELECT id FROM estudiantes WHERE cedula_provisional = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cedula);
$stmt->execute();
$res = $stmt->get_result();

echo json_encode(["existe" => $res->num_rows > 0]);
