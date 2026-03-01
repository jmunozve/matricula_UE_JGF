<?php
/**
 * modulos/controllers/representante/GuardarRepresentante.php
 */
session_start();

// Importamos tu archivo de conexión centralizada
require_once dirname(__DIR__, 3) . '/includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. VALIDACIÓN DEL TOKEN CSRF (Seguridad contra ataques externos)
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error de seguridad: Solicitud no válida.");
    }

    // 2. CAPTURA Y SANITIZACIÓN DE DATOS (Limpieza de basura)
    // Usamos preg_replace para asegurar que la cédula y el contacto sean puros números
    $cedula     = preg_replace('/[^0-9]/', '', $_POST['cedula_rep'] ?? '');
    $nombre     = strtoupper(trim($_POST['nombre'] ?? ''));
    $apellido   = strtoupper(trim($_POST['apellido'] ?? ''));
    $contacto   = preg_replace('/[^0-9]/', '', $_POST['contacto'] ?? '');
    $parentesco = strtoupper(trim($_POST['parentesco'] ?? ''));

    // Validación mínima obligatoria
    if (empty($cedula) || empty($nombre) || empty($apellido)) {
        die("Error: Los campos de Cédula, Nombre y Apellido son obligatorios.");
    }

    try {
        // USAMOS TU MÉTODO: Conexion::abrir()
        $pdo = Conexion::abrir();

        // 3. VERIFICAR SI EL REPRESENTANTE YA EXISTE
        // Como la cédula es UNIQUE en tu DB, esto evita errores de duplicidad
        $stmtCheck = $pdo->prepare("SELECT id_representante FROM representantes WHERE cedula_rep = ? LIMIT 1");
        $stmtCheck->execute([$cedula]);
        $representante = $stmtCheck->fetch();

        if ($representante) {
            // SI EXISTE: Actualizamos sus datos (por si cambiaron)
            $id_representante = $representante['id_representante'];
            $sql = "UPDATE representantes SET nombre = ?, apellido = ?, contacto = ?, parentesco = ? WHERE id_representante = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $apellido, $contacto, $parentesco, $id_representante]);
        } else {
            // NO EXISTE: Creamos el nuevo registro
            $sql = "INSERT INTO representantes (nombre, apellido, contacto, parentesco, cedula_rep) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $apellido, $contacto, $parentesco, $cedula]);
            $id_representante = $pdo->lastInsertId();
        }

        // 4. PERSISTENCIA EN SESIÓN
        // Guardamos el ID para usarlo luego en el registro del estudiante (FK)
// ... después de guardar exitosamente ...

$_SESSION['ultimo_representante_id'] = $id_representante;
$_SESSION['ultimo_representante_nombre'] = $nombre . " " . $apellido;

// REDIRECCIÓN CORREGIDA:
// Usamos la ruta completa desde la carpeta del proyecto
header("Location: /matricula/router.php?ruta=estudiante/crear");
exit();

    } catch (Exception $e) {
        // Tu clase Conexion::abrir() ya registra los errores en /logs/errores_db.log
        die($e->getMessage());
    }
}