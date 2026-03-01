<?php
// F:\xampp\htdocs\matricula\modulos\controllers\secciones\obtener_cupos_disponibles.php
require_once '../../../includes/db.php'; 

header('Content-Type: application/json');

try {
    $pdo = Conexion::abrir();
    
    // Query optimizada: Trae nombre del grado, letra, turno, capacidad y cuenta inscritos
    $sql = "SELECT 
                g.id_grado, 
                s.id_seccion, 
                g.nombre_grado, 
                s.letra, 
                s.turno, 
                s.capacidad_max,
                (SELECT COUNT(*) FROM estudiantes e WHERE e.id_seccion = s.id_seccion) as total_inscritos
            FROM secciones s
            JOIN grados g ON s.id_grado = g.id_grado
            ORDER BY g.id_grado ASC, s.letra ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}