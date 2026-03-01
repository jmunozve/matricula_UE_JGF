<?php
// parroquias.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/init.php'; // ← Centraliza sesión, conexión y funciones

$pdo = Conexion::abrir();

$id_municipio = $_GET['id_municipio'] ?? null;

if ($id_municipio) {
    $stmt = $pdo->prepare("SELECT id_parroquia AS id, nombre FROM cat_parroquias WHERE id_municipio = ?");
    $stmt->execute([$id_municipio]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo json_encode([]);
}


