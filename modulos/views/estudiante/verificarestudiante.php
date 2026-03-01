<?php
/**
 * verificarestudiante.php - Comprobación con soporte para lógica de gemelos
 */
header('Content-Type: application/json; charset=utf-8');

error_reporting(0);
ini_set('display_errors', 0);

require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';

try {
    $pdo = Conexion::abrir();
    if (!$pdo) throw new Exception("Error de conexión al servidor local.");

    $cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';

    if (empty($cedula)) {
        echo json_encode(["existe" => false, "mensaje" => "No se proporcionó identificación."]);
        exit;
    }

    // 1. Buscamos si el estudiante YA existe
    $sql = "SELECT e.nombre, e.apellido, e.cedula, e.cedula_escolar, 
                   r.nombre AS rep_nom, r.apellido AS rep_ape 
            FROM estudiante e 
            LEFT JOIN representantes r ON e.id_representante = r.id_representante 
            WHERE e.cedula = ? OR e.cedula_escolar = ? 
            LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cedula, $cedula]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($estudiante) {
        echo json_encode([
            "existe" => true,
            "nombre" => $estudiante['nombre'],
            "apellido" => $estudiante['apellido'],
            "identificacion" => $estudiante['cedula'] ?: $estudiante['cedula_escolar'],
            "nombre_representante" => ($estudiante['rep_nom']) ? $estudiante['rep_nom'] . " " . $estudiante['rep_ape'] : "No asignado"
        ]);
    } else {
        /**
         * 2. LÓGICA PROACTIVA PARA GEMELOS:
         * Si no existe el alumno, verificamos si la cédula ingresada es de un representante
         * para sugerir el número de hijo (prefijo de cédula escolar).
         */
        $sql_count = "SELECT COUNT(*) as total FROM estudiante WHERE id_representante = (SELECT id_representante FROM representantes WHERE cedula = ? LIMIT 1)";
        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->execute([$cedula]);
        $conteo = $stmt_count->fetch(PDO::FETCH_ASSOC);
        
        // Sugerimos el siguiente número: si hay 0, el siguiente es 1. Si hay 1, el siguiente es 2.
        $sugerencia_prefijo = ($conteo) ? ($conteo['total'] + 1) : 1;

        echo json_encode([
            "existe" => false,
            "mensaje" => "Disponible para inscripción",
            "sugerencia_prefijo" => $sugerencia_prefijo
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["existe" => false, "error" => $e->getMessage()]);
}