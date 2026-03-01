<?php
/**
 * modulos/controllers/usuarios/guardar_usuario.php
 */
session_start(); // ¡Importante para verificar el rol!
header('Content-Type: application/json');

// 1. Verificación de Seguridad: Solo Admins
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin') {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado: No tiene permisos de administrador.']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';

// Iniciamos respuesta estándar
$response = ['status' => 'error', 'message' => 'Error desconocido'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = Conexion::abrir();

        // --- CASO 1: CAMBIO DE ESTADO (VÍA SWITCH) ---
        if (isset($_POST['accion']) && $_POST['accion'] === 'toggle_estado') {
            $id_switch = intval($_POST['id_usuario']);
            $nuevo_estado = intval($_POST['nuevo_estado']);

            $sql_status = "UPDATE usuarios SET estado = :est WHERE id = :id";
            $stmt_status = $pdo->prepare($sql_status);
            $stmt_status->execute(['est' => $nuevo_estado, 'id' => $id_switch]);

            echo json_encode(['status' => 'success', 'message' => 'Estado actualizado']);
            exit;
        }

        // --- CASO 2: RESTABLECER CONTRASEÑA (CÉDULA COMO CLAVE) ---
        if (isset($_POST['accion']) && $_POST['accion'] === 'reset_password') {
            $id_user = intval($_POST['id_usuario']);
            
            // Consultamos la cédula para usarla como nueva clave hash
            $stmt_get = $pdo->prepare("SELECT cedula FROM usuarios WHERE id = ?");
            $stmt_get->execute([$id_user]);
            $u_data = $stmt_get->fetch(PDO::FETCH_ASSOC);

            if ($u_data) {
                $nueva_clave = password_hash($u_data['cedula'], PASSWORD_BCRYPT);
                $stmt_upd = $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
                $stmt_upd->execute([$nueva_clave, $id_user]);

                echo json_encode(['status' => 'success', 'message' => 'Contraseña restablecida a: ' . $u_data['cedula']]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
            }
            exit;
        }

        // --- CASO 3: GUARDAR O EDITAR USUARIO ---
        $id = !empty($_POST['id_usuario']) ? intval($_POST['id_usuario']) : null;
        $nombre = trim($_POST['nombre_usuario'] ?? '');
        $cedula = trim($_POST['cedula'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $rol = $_POST['rol'] ?? '';

        if (empty($nombre) || empty($cedula) || empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
            exit;
        }

        // Validar Duplicados
        $check_sql = "SELECT cedula, email FROM usuarios WHERE (cedula = :ced OR email = :em)";
        if ($id) $check_sql .= " AND id != :id";
        
        $check_stmt = $pdo->prepare($check_sql);
        $params = ['ced' => $cedula, 'em' => $email];
        if ($id) $params['id'] = $id;
        
        $check_stmt->execute($params);
        $duplicado = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($duplicado) {
            $msg = ($duplicado['cedula'] === $cedula) ? 'La cédula ya existe.' : 'El correo ya está en uso.';
            echo json_encode(['status' => 'error', 'message' => $msg]);
            exit;
        }

        if (!$id) {
            // MODO CREAR
            if (empty($_POST['contrasena'])) {
                echo json_encode(['status' => 'error', 'message' => 'La contraseña es obligatoria.']);
                exit;
            }
            $pass_hashed = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
            $sql = "INSERT INTO usuarios (nombre_usuario, cedula, email, contrasena, rol, estado) 
                    VALUES (:nom, :ced, :em, :pass, :rol, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['nom'=>$nombre, 'ced'=>$cedula, 'em'=>$email, 'pass'=>$pass_hashed, 'rol'=>$rol]);
            $response = ['status' => 'success', 'message' => 'Usuario creado con éxito.'];
        } else {
            // MODO EDITAR
            $sql = "UPDATE usuarios SET nombre_usuario=:nom, cedula=:ced, email=:em, rol=:rol WHERE id=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['nom'=>$nombre, 'ced'=>$cedula, 'em'=>$email, 'rol'=>$rol, 'id'=>$id]);
            $response = ['status' => 'success', 'message' => 'Usuario actualizado correctamente.'];
        }

    } catch (PDOException $e) {
        $response = ['status' => 'error', 'message' => 'Error BD: ' . $e->getMessage()];
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
    }
}

echo json_encode($response);