<?php
// municipios.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/init.php'; // ← Centraliza sesión, conexión y funciones

$pdo = Conexion::abrir();

$id_estado = $_GET['id_estado'] ?? null;

if ($id_estado) {
    $stmt = $pdo->prepare("SELECT id_municipio AS id, nombre FROM cat_municipios WHERE id_estado = ?");
    $stmt->execute([$id_estado]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo json_encode([]);
}



