<?php
/**
 * verificarestudiante.php - Control de Duplicados por Familia
 */
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';

try {
    $pdo = Conexion::abrir();
    $cedula_generada = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
    $ci_rep = isset($_POST['cedula_rep']) ? trim($_POST['cedula_rep']) : '';

    if (empty($cedula_generada) || empty($ci_rep)) {
        echo json_encode(["existe" => false, "mensaje" => "Datos incompletos para validar."]);
        exit;
    }

    // --- LÓGICA CRÍTICA PARA LA TESIS ---
    // Buscamos si ese ID ya existe vinculado a ESTE representante específicamente.
    // Esto permite que el número "1" se repita en familias diferentes.
    $sql = "SELECT e.nombre, e.apellido 
            FROM estudiante e 
            INNER JOIN representantes r ON e.id_representante = r.id_representante 
            WHERE (e.cedula = ? OR e.cedula_escolar = ?) 
            AND r.cedula_rep = ? LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cedula_generada, $cedula_generada, $ci_rep]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($estudiante) {
        echo json_encode([
            "existe" => true,
            "detalle" => "Ya registró a {$estudiante['nombre']} con este número de hijo."
        ]);
    } else {
        // Si no existe, sugerimos el siguiente número disponible para este representante
        $sql_count = "SELECT COUNT(*) as total FROM estudiante 
                      WHERE id_representante = (SELECT id_representante FROM representantes WHERE cedula_rep = ? LIMIT 1)";
        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->execute([$ci_rep]);
        $conteo = $stmt_count->fetch(PDO::FETCH_ASSOC);
        
        $sugerencia = ($conteo['total'] > 0) ? ($conteo['total'] + 1) : 1;

        echo json_encode([
            "existe" => false,
            "sugerencia_prefijo" => $sugerencia
        ]);
    }
} catch (Exception $e) {
    echo json_encode(["existe" => false, "error" => $e->getMessage()]);
}