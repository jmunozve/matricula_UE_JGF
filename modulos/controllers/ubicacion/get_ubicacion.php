<?php

/**
 * get_ubicacion.php - CORREGIDO PARA NIVEL 2 Y PLAN 4 (PRIMARIA)
 */
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) session_start();

$ruta_db = __DIR__ . "/../../../includes/db.php";
if (!file_exists($ruta_db)) {
    echo json_encode(["error" => "No se encontró db.php"]);
    exit;
}
require_once $ruta_db;

try {
    $pdo = Conexion::abrir();
    $tipo = $_GET['tipo'] ?? '';
    $id = intval($_GET['id'] ?? 0);

    // Identificación del plantel
    $codigo_plantel = $_SESSION['codigo_plantel'] ?? 'OD06400102';

    if ($id <= 0 && !in_array($tipo, ['secciones_todas', 'grados'])) {
        echo json_encode([]);
        exit;
    }

    $query = "";
    $params = [];

    switch ($tipo) {
        // ==========================================
        // CASOS ACADÉMICOS (Primaria Nivel 2, Plan 4)
        // ==========================================
        case 'grados':
            // Obtenemos los grados que pertenecen al nivel 2 (vía plan 4)
            // Esto asegura que cargue del 6 al 11
            $query = "SELECT id_grado AS id, nombre_grado AS nombre 
                      FROM grados 
                      WHERE id_plan = 4 
                      ORDER BY id_grado ASC";
            $params = [];
            break;

        case 'areas':
            // Buscamos las materias usando el id_plan 4 y el id_grado recibido (6-11)
            $query = "SELECT id_area AS id, nombre_area AS nombre 
                      FROM areas_formacion 
                      WHERE id_plan = 4 AND id_grado = ? 
                      ORDER BY orden ASC";
            $params = [$id];
            break;

        case 'secciones':

            $query = "SELECT 
                id_seccion AS id, 
                id_seccion,
                letra, 
                turno, 
                capacidad_max AS capacidad, 
                capacidad_max,
                CONCAT('SECCIÓN ', letra, ' (', turno, ')') AS nombre 
              FROM secciones 
              WHERE id_grado = ? AND estatus = 'Activo' 
              ORDER BY letra ASC";
            $params = [$id];
            break;

        // ==========================================
        // CASOS GEOGRÁFICOS
        // ==========================================
        case 'municipios':
            $query = "SELECT id_municipio AS id, nombre_municipio AS nombre FROM municipios WHERE id_estado = ? ORDER BY nombre_municipio ASC";
            $params = [$id];
            break;

        case 'parroquias':
            $query = "SELECT id_parroquia AS id, nombre_parroquia AS nombre FROM parroquias WHERE id_municipio = ? ORDER BY nombre_parroquia ASC";
            $params = [$id];
            break;

        default:
            echo json_encode(["error" => "Tipo '$tipo' no reconocido"]);
            exit;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode(["error" => "Error SQL: " . $e->getMessage()]);
}
