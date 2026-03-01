<?php
/**
 * modulos/controllers/admin/usuarios/UsuarioController.php
 */
require_once "../../../../includes/db.php"; // Subimos 4 niveles
session_start();

// Validación de seguridad: Solo Admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin') {
    echo json_encode(['status' => 'error', 'msg' => 'Acceso no autorizado']);
    exit;
}

header('Content-Type: application/json');

try {
    $pdo = Conexion::abrir();
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'guardar':
            $id = $_POST['id'] ?? '';
            $nombre_usuario = $_POST['nombre_usuario'];
            $cedula = $_POST['cedula'];
            $email = $_POST['email'];
            $rol = $_POST['rol'];

            if (empty($id)) {
                // INSERTAR
                $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
                
                $check = $pdo->prepare("SELECT id FROM usuarios WHERE cedula = ? OR email = ? OR nombre_usuario = ?");
                $check->execute([$cedula, $email, $nombre_usuario]);
                if ($check->fetch()) {
                    echo json_encode(['status' => 'error', 'msg' => 'Cédula, Email o Usuario ya registrados']);
                    exit;
                }

                $sql = "INSERT INTO usuarios (nombre_usuario, cedula, email, contrasena, rol) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre_usuario, $cedula, $email, $contrasena, $rol]);
                echo json_encode(['status' => 'success', 'msg' => 'Usuario creado correctamente']);
            } else {
                // ACTUALIZAR
                $sql = "UPDATE usuarios SET nombre_usuario = ?, cedula = ?, email = ?, rol = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre_usuario, $cedula, $email, $rol, $id]);
                echo json_encode(['status' => 'success', 'msg' => 'Usuario actualizado correctamente']);
            }
            break;

        case 'reset_pass':
            $id = $_POST['id'];
            $nueva_pass = password_hash("123456", PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET contrasena = ? WHERE id = ?";
            $pdo->prepare($sql)->execute([$nueva_pass, $id]);
            echo json_encode(['status' => 'success', 'msg' => 'Contraseña restablecida a: 123456']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}