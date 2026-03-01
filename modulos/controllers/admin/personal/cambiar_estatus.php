<?php
/**
 * ARCHIVO: cambiar_estatus.php
 * RUTA: /modulos/controllers/admin/personal/cambiar_estatus.php
 * DESCRIPCIÓN: Cambia el estado (Activo/Inactivo) del personal institucional.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. SEGURIDAD: Solo Directivos, Coordinadores y Superusuarios
$rol_limpio = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : '';
$roles_autorizados = ['superusuario', 'director', 'directora', 'coordinador', 'coordinadora'];

if (!in_array($rol_limpio, $roles_autorizados)) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Acceso no autorizado."]);
    exit();
}

// 2. CONEXIÓN
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';

// 3. VALIDAR DATOS (Recibidos por POST desde el JS de la tabla)
if (isset($_POST['id']) && isset($_POST['estatus'])) {
    
    $id_personal = intval($_POST['id']);
    $nuevo_estatus = $_POST['estatus']; // 'Activo' o 'Inactivo'

    try {
        $db = Conexion::abrir();

        // 4. ACTUALIZAR EN LA TABLA 'personal'
        // Nota: id_docente es la PRI de tu tabla según tu DESC
        $sql = "UPDATE personal SET estatus = :estatus WHERE id_docente = :id";
        $stmt = $db->prepare($sql);
        
        $resultado = $stmt->execute([
            'estatus' => $nuevo_estatus,
            'id'      => $id_personal
        ]);

        header('Content-Type: application/json');
        if ($resultado) {
            echo json_encode(["status" => "success", "message" => "Estatus actualizado a $nuevo_estatus"]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se pudo actualizar el registro."]);
        }
        exit();

    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
    exit();
}