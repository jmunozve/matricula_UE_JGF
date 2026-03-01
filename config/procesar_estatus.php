<?php
/**
 * F:\xampp\htdocs\matricula\config\procesar_estatus.php
 * Procesa el cambio de estado usando la columna 'estatus' (Activo/Inactivo)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. SEGURIDAD: Solo Superusuario o Admin
$rol_limpio = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : '';

if ($rol_limpio !== 'superusuario' && $rol_limpio !== 'admin') {
    die("Acceso no autorizado.");
}

// 2. CONEXIÓN
require_once "../includes/db.php"; 

try {
    $db = Conexion::abrir(); 
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 3. VALIDAR DATOS RECIBIDOS
if (isset($_GET['id']) && isset($_GET['estado'])) {
    $id_nivel = intval($_GET['id']);
    
    // Convertimos el 0/1 que viene del JS a los strings que usa tu DB
    $nuevo_estatus = (intval($_GET['estado']) === 1) ? 'Activo' : 'Inactivo';

    try {
        // 4. ACTUALIZAR EN LA BASE DE DATOS
        // Usamos la columna 'estatus' para no romper la compatibilidad
        $sql = "UPDATE niveles_estudio SET estatus = :estatus WHERE id_nivel = :id";
        $stmt = $db->prepare($sql);
        
        $stmt->execute([
            'estatus' => $nuevo_estatus,
            'id'      => $id_nivel
        ]);

        // 5. REDIRIGIR CON ÉXITO
        header("Location: gestion_planes.php?success=1");
        exit();

    } catch (PDOException $e) {
        die("Error al actualizar la base de datos: " . $e->getMessage());
    }
} else {
    // Si alguien entra al archivo sin datos, lo devolvemos
    header("Location: gestion_planes.php");
    exit();
}