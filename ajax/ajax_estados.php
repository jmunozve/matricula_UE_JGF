<?php
// estados.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/init.php'; // ← Centraliza sesión, conexión y funciones

$pdo = Conexion::abrir();

$id_pais = $_GET['id_pais'] ?? null;

if ($id_pais) {
    $stmt = $pdo->prepare("SELECT id_estado AS id, nombre FROM cat_estados WHERE id_pais = ?");
    $stmt->execute([$id_pais]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo json_encode([]);
}

