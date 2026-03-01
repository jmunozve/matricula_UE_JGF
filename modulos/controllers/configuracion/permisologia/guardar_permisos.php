<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/loader.php';

if (!isset($_SESSION['usuario']) || ($_SESSION['usuario']['rol'] ?? '') !== 'admin') {
  exit('⛔ Acceso denegado.');
}

$id_usuario = $_POST['id_usuario'] ?? null;
$permisos = $_POST['permisos'] ?? [];

if (!$id_usuario || !is_numeric($id_usuario)) {
  exit('⛔ Usuario no válido.');
}

// 🔄 Eliminar permisos actuales
$stmtDel = $pdo->prepare("DELETE FROM usuario_permisos WHERE id_usuario = ?");
$stmtDel->execute([$id_usuario]);

// ✅ Insertar nuevos permisos
$stmtIns = $pdo->prepare("INSERT INTO usuario_permisos (id_usuario, id_permiso) VALUES (?, ?)");
foreach ($permisos as $id_permiso) {
  if (is_numeric($id_permiso)) {
    $stmtIns->execute([$id_usuario, $id_permiso]);
  }
}

header("Location: /matricula/router.php?ruta=configuracion/permisologia&mensaje=Permisos actualizados correctamente");
exit;


