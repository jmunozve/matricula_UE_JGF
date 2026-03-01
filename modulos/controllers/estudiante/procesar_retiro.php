<?php
// 1. Asegurar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Limpiar cualquier salida previa para que el JSON sea puro
while (ob_get_level()) {
    ob_end_clean();
}
header('Content-Type: application/json');

// 3. Configuración de errores (ocultos para no dañar el JSON)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 4. Importar base de datos
require_once "../../../includes/db.php";

$res = ["status" => "error", "msg" => "Error desconocido"];

// 5. Capturar usuario de sesión o asignar uno por defecto para que NO falle
$usuario = $_SESSION['usuario'] ?? $_SESSION['user'] ?? "Sistema";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validar datos mínimos
    $id_estudiante = $_POST['id_estudiante'] ?? '';
    $id_motivo     = $_POST['id_motivo'] ?? '';
    $fecha_retiro  = $_POST['fecha_retiro'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';

    if (empty($id_estudiante) || empty($id_motivo) || empty($fecha_retiro)) {
        echo json_encode(["status" => "error", "msg" => "Faltan datos obligatorios para procesar el retiro."]);
        exit;
    }

    try {
        // Asumiendo que tu clase en db.php se llama Conexion y el método es abrir()
        $pdo = Conexion::abrir();
        $pdo->beginTransaction(); 

        // 1. Insertar el historial de retiro
        $sqlInsert = "INSERT INTO retiros_estudiantes 
                      (id_estudiante, id_motivo, fecha_retiro, observaciones, registrado_por) 
                      VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([$id_estudiante, $id_motivo, $fecha_retiro, $observaciones, $usuario]);

        // 2. Actualizar estatus y liberar cupo en la tabla estudiante
        $sqlUpdate = "UPDATE estudiantes 
                      SET estatus = 'Retirado', 
                          id_seccion = NULL 
                      WHERE id_estudiante = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$id_estudiante]);

        $pdo->commit(); 

        echo json_encode([
            "status" => "success", 
            "msg" => "Estudiante retirado exitosamente. El cupo ha sido liberado.",
            "id_estudiante" => $id_estudiante
        ]);

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack(); 
        }
        echo json_encode(["status" => "error", "msg" => "Error de base de datos: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "msg" => "Acceso no permitido."]);
}
exit;