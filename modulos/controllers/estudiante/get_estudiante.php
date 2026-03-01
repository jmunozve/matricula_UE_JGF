<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desactivamos errores visuales para no romper el JSON

// Intentos de ruta para el archivo de conexión (siguiendo tu lógica)
$intentos = [
    dirname(__DIR__, 3).'/includes/db.php',
    "../../../includes/db.php"
];

$path_db = null;
foreach ($intentos as $ruta) {
    if (file_exists($ruta)) {
        $path_db = $ruta;
        break;
    }
}

if (!$path_db) {
    echo json_encode(["status" => "error", "msg" => "No se encontró db.php"]);
    exit;
}

require_once $path_db;

try {
    $pdo = Conexion::abrir();
    
    // Validar que llegue el ID
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode(["status" => "error", "msg" => "ID de estudiante no proporcionado."]);
        exit;
    }

    // Consulta detallada para traer todo lo necesario a la modal
    // Incluimos campos de cédula, sexo y estatus para lógica futura
    $sql = "SELECT id_estudiante, nombre, apellido, cedula, cedula_escolar, sexo, estatus 
            FROM estudiante 
            WHERE id_estudiante = ? 
            LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($estudiante) {
        // Retornamos los datos directamente (el JS de la modal los espera así)
        echo json_encode($estudiante);
    } else {
        echo json_encode(["status" => "error", "msg" => "Estudiante no encontrado."]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "msg" => "Error de servidor: " . $e->getMessage()]);
}