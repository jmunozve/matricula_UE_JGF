<?php
/**
 * modulos/controllers/secciones/borrar_seccion.php
 * Respuesta en JSON para ser capturada por SweetAlert2/Fetch
 */
header('Content-Type: application/json'); // Importante para la Modal
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../../../includes/db.php";

$response = ['status' => 'error', 'message' => 'Solicitud no válida'];

// Validamos que sea una petición POST o tenga ID
$id_seccion = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id_seccion) {
    echo json_encode($response);
    exit;
}

try {
    $pdo = Conexion::abrir();

    // 1. Verificar si tiene estudiantes inscritos
    $check = $pdo->prepare("SELECT COUNT(*) FROM estudiantes WHERE id_seccion = ?");
    $check->execute([$id_seccion]);
    $total = $check->fetchColumn();

    if ($total > 0) {
        $response = [
            'status' => 'warning',
            'message' => "No se puede eliminar: La sección tiene $total estudiantes inscritos."
        ];
    } else {
        $pdo->beginTransaction();
        
        // 2. Limpiar historial de docentes (evitar error de integridad)
        $delHistorial = $pdo->prepare("DELETE FROM historial_docentes_secciones WHERE id_seccion = ?");
        $delHistorial->execute([$id_seccion]);

        // 3. Eliminar la sección
        $delete = $pdo->prepare("DELETE FROM secciones WHERE id_seccion = ?");
        $delete->execute([$id_seccion]);
        
        $pdo->commit();

        $response = [
            'status' => 'success',
            'message' => 'Sección eliminada correctamente.'
        ];
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    $response = [
        'status' => 'error',
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ];
}

echo json_encode($response);