<?php
// F:\xampp\htdocs\matricula\modulos\controllers\academico\obtener_filtros.php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';

try {
    $pdo = Conexion::abrir();

    $tipo = $_GET['tipo'] ?? '';
    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode([]);
        exit;
    }

    if ($tipo == 'grados') {
        // Obtenemos grados filtrados por el ID del nivel
        $stmt = $pdo->prepare("SELECT id_grado, nombre_grado FROM grados WHERE id_nivel = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } 
    elseif ($tipo == 'secciones') {
        // Calculamos ocupación real filtrando por grado
        $sql = "SELECT s.id_seccion, s.letra, s.turno, s.capacidad_max,
                (SELECT COUNT(*) FROM estudiante e WHERE e.id_seccion = s.id_seccion AND e.estatus = 'Inscrito') as ocupados
                FROM secciones s 
                WHERE s.id_grado = ? AND s.estatus = 'Activo'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo json_encode(['error' => 'Tipo de filtro no válido']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}