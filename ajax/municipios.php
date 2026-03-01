<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/init.php'; // ← Centraliza sesión, conexión y funciones


header('Content-Type: application/json');

try {
    // Validar parámetro
    if (!isset($_GET['id_estado']) || !is_numeric($_GET['id_estado'])) {
        throw new Exception('ID de estado no válido');
    }

    $id_estado = (int)$_GET['id_estado'];
    $conn = conectarDB();

    // Consulta preparada para seguridad
    $query = "SELECT id_municipio, nombre FROM municipios WHERE id_estado = ? ORDER BY nombre";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Error al preparar consulta: ' . $conn->error);
    }

    $stmt->bind_param("i", $id_estado);
    $stmt->execute();
    $result = $stmt->get_result();

    $municipios = [];
    while ($row = $result->fetch_assoc()) {
        $municipios[] = [
            'id' => $row['id_municipio'],
            'nombre' => htmlspecialchars($row['nombre'])
        ];
    }

    if (empty($municipios)) {
        $municipios[] = [
            'id' => '',
            'nombre' => 'No hay municipios registrados'
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $municipios
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}
?>