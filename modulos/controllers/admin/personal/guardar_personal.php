<?php
/**
 * ARCHIVO: guardar_personal.php
 * DESCRIPCIÓN: Procesa la inserción y edición con el nuevo ID de especialidad.
 */

if (ob_get_length()) ob_clean();

require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recibimos y limpiamos datos
    $id_docente      = !empty($_POST['id_docente']) ? intval($_POST['id_docente']) : null;
    $cedula          = trim($_POST['cedula']);
    $nombre          = trim($_POST['nombre']);
    $apellido        = trim($_POST['apellido']);
    $cargo           = $_POST['cargo'];
    // CAMBIO: Ahora recibimos el ID numérico de la especialidad
    $id_especialidad = !empty($_POST['id_especialidad']) ? intval($_POST['id_especialidad']) : null;
    $telefono        = trim($_POST['telefono']);
    $email           = trim($_POST['email']);

    try {
        $pdo = Conexion::abrir();

        // 1. Validar Cédula Duplicada
        $sql_check = "SELECT id_docente FROM personal WHERE cedula = ? AND id_docente != ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$cedula, $id_docente ?? 0]);
        
        if ($stmt_check->fetch()) {
            echo json_encode(["status" => "error", "message" => "La cédula $cedula ya pertenece a otro trabajador."]);
            exit();
        }

        if ($id_docente) {
            // --- MODO EDICIÓN ---
            // CAMBIO: Se actualiza id_especialidad en lugar de especialidad (texto)
            $sql = "UPDATE personal SET 
                        nombre = ?, 
                        apellido = ?, 
                        cedula = ?, 
                        cargo = ?, 
                        id_especialidad = ?, 
                        telefono = ?,
                        email = ?
                    WHERE id_docente = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $apellido, $cedula, $cargo, $id_especialidad, $telefono, $email, $id_docente]);
            $mensaje = "Datos del trabajador actualizados correctamente.";
            
        } else {
            // --- MODO REGISTRO NUEVO ---
            // CAMBIO: Se inserta id_especialidad
            $sql = "INSERT INTO personal (nombre, apellido, cedula, cargo, id_especialidad, telefono, email, estatus) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $apellido, $cedula, $cargo, $id_especialidad, $telefono, $email]);
            $mensaje = "Personal registrado exitosamente en el sistema.";
        }

        echo json_encode(["status" => "success", "message" => $mensaje]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error de BD: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Acceso no autorizado."]);
}