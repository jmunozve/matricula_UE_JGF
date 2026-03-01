<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/init.php'; // ← Centraliza sesión, conexión y funciones

header('Content-Type: application/json');

try {
    // Validar parámetro
    if (!isset($_GET['id_municipio']) || !is_numeric($_GET['id_municipio'])) {
        throw new Exception('ID de municipio no válido');
    }

    $id_municipio = (int)$_GET['id_municipio'];
    $pdo= conectarDB();

    // Consulta preparada para seguridad
    $query = "SELECT id_parroquia, nombre FROM parroquias WHERE id_municipio = ? ORDER BY nombre";
    $stmt = $pdo->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Error al preparar consulta: ' . $conn->error);
    }

    $stmt->bind_param("i", $id_municipio);
    $stmt->execute();
    $result = $stmt->get_result();

    $parroquias = [];
    while ($row = $result->fetch_assoc()) {
        $parroquias[] = [
            'id' => $row['id_parroquia'],
            'nombre' => htmlspecialchars($row['nombre'])
        ];
    }

    if (empty($parroquias)) {
        $parroquias[] = [
            'id' => '',
            'nombre' => 'No hay parroquias registradas'
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $parroquias
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($pdo)) $pdo->close();
}
?>