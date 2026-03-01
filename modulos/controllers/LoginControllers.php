<?php
/**
 * modulos/controllers/LoginControllers.php
 * Controlador de autenticación de usuarios - ACTUALIZADO
 */
session_start();
require_once('../../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    // Ruta base para redirecciones
    $url_login = "/matricula/modulos/views/login.php";

    // 1. Validación de campos vacíos
    if (empty($cedula) || empty($contrasena)) {
        $_SESSION['mensaje_login'] = 'Por favor, ingrese su cédula y contraseña.';
        header("Location: $url_login");
        exit();
    }

    try {
        $db = Conexion::abrir();
        
        // 2. Buscamos al usuario por cédula
        $sql = "SELECT id, nombre_usuario, cedula, contrasena, rol, estado 
                FROM usuarios 
                WHERE cedula = :cedula 
                LIMIT 1";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['cedula' => $cedula]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Verificamos si existe el usuario
        if ($usuario) {
            
            // A. Verificación de estado (0 = Inactivo, 1 = Activo)
            if (isset($usuario['estado']) && $usuario['estado'] == 0) {
                $_SESSION['mensaje_login'] = 'Acceso denegado: Su cuenta está inactiva.';
                header("Location: $url_login");
                exit();
            }

            // B. Verificación de Contraseña (Bcrypt)
            if (password_verify($contrasena, $usuario['contrasena'])) {
                
                // --- LOGIN EXITOSO ---
                session_regenerate_id(true);

                $_SESSION['id_usuario']     = $usuario['id'];
                $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
                
                /**
                 * IMPORTANTE: Guardamos el rol tal cual viene de la DB.
                 * El dashboard usará strtolower() para comparar, 
                 * así que no importa si en la DB dice 'Superusuario' o 'superusuario'.
                 */
                $_SESSION['rol']            = $usuario['rol']; 
                $_SESSION['ultimo_acceso']  = date("Y-m-d H:i:s");

                // Redirección al área principal
                header("Location: /matricula/modulos/views/dashboard.php");
                exit();

            } else {
                $_SESSION['mensaje_login'] = 'La cédula o la contraseña son incorrectas.';
                header("Location: $url_login");
                exit();
            }

        } else {
            $_SESSION['mensaje_login'] = 'Los datos ingresados no coinciden con nuestros registros.';
            header("Location: $url_login");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['mensaje_login'] = 'Error de conexión con la base de datos.';
        header("Location: $url_login");
        exit();
    }
} else {
    header("Location: /matricula/modulos/views/login.php");
    exit();
}