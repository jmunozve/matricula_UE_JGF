<?php
/**
 * F:\xampp\htdocs\matricula\modulos\controllers\estudiante\obtener_estudiante.php
 * VERSIÓN CORREGIDA: Unificación de Nacimiento y Dirección + Joins completos
 */
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';

if (isset($_GET['id'])) {
    try {
        $pdo = Conexion::abrir();
        
        $sql = "SELECT 
                    e.*, 
                    -- Datos del Representante
                    r.nombre as rep_nombre, 
                    r.apellido as rep_apellido, 
                    r.cedula_rep,
                    -- Ubicación de Nacimiento
                    pnac.nombre_pais as pais_nacimiento,
                    enac.nombre_estado as estado_nacimiento,
                    mnac.nombre_municipio as municipio_nacimiento,
                    pnac_arr.nombre_parroquia as parroquia_nacimiento,
                    -- Ubicación de Habitación (Dirección)
                    ehab.nombre_estado as estado_habitacion,
                    mhab.nombre_municipio as municipio_habitacion,
                    phab.nombre_parroquia as parroquia_habitacion
                FROM estudiante e
                LEFT JOIN representantes r ON e.id_representante = r.id_representante
                -- Joins Nacimiento
                LEFT JOIN paises pnac ON e.id_pais_es = pnac.id_pais
                LEFT JOIN estados enac ON e.id_estado_nac = enac.id_estado
                LEFT JOIN municipios mnac ON e.id_mun_nac = mnac.id_municipio
                LEFT JOIN parroquias pnac_arr ON e.id_parroquia_nac = pnac_arr.id_parroquia
                -- Joins Habitación (Dirección)
                LEFT JOIN estados ehab ON e.id_estado_hab = ehab.id_estado
                LEFT JOIN municipios mhab ON e.id_mun_hab = mhab.id_municipio
                LEFT JOIN parroquias phab ON e.id_parroquia_hab = phab.id_parroquia
                WHERE e.id_estudiante = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_GET['id']]);
        $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($estudiante) {
            // Protección contra nulls para el frontend (evita que JS se rompa)
            foreach ($estudiante as $key => $value) {
                $estudiante[$key] = $value ?? "";
            }
            
            // Aseguramos compatibilidad de nombres de campos con el JS de la vista
            $estudiante['fecha_nac'] = $estudiante['fecha_nacimiento'];
            
            echo json_encode($estudiante);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Estudiante no encontrado']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de BD: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID no proporcionado']);
}