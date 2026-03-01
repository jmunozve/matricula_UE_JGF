<?php
// Limpiar cualquier salida previa para asegurar un JSON limpio
ob_clean();
header('Content-Type: application/json; charset=utf-8');

// Ajusta esta ruta si es necesario, debe llegar a tu db.php
require_once "../../includes/db.php"; 

try {
    $pdo = Conexion::abrir();
    
    $id_nivel = isset($_GET['id_nivel']) ? intval($_GET['id_nivel']) : 0;

    if ($id_nivel > 0) {
        // Asegúrate que la tabla se llama 'grados' y las columnas son estas
        $stmt = $pdo->prepare("SELECT id_grado, nombre_grado FROM grados WHERE id_nivel = ? ORDER BY id_grado ASC");
        $stmt->execute([$id_nivel]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($resultados ? $resultados : []);
    } else {
        echo json_encode([]);
    }

} catch (Exception $e) {
    // Si algo falla, enviamos el error en formato JSON para que JS no explote
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
exit;