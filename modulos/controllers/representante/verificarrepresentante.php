<?php
/**
 * verificarrepresentante.php - CONTROLADOR DE BÚSQUEDA
 * Ubicación: F:\xampp\htdocs\matricula\modulos\controllers\representante\verificarrepresentante.php
 */

// Limpiamos cualquier salida previa para evitar errores en el JSON
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../../../includes/db.php'; 

$cedulaInput = $_GET['cedula'] ?? '';
// Solo números para evitar inyecciones o errores
$cedula = preg_replace('/[^0-9]/', '', $cedulaInput);

if (empty($cedula)) {
    ob_clean();
    echo json_encode(['existe' => false, 'error' => 'Cédula vacía']);
    exit;
}

try {
    $pdo = Conexion::abrir(); 

    // 1. Buscamos los datos exactos del representante
    // Nota: Usamos nombre_rep y apellido_rep según la estructura de tu tabla
    $query = $pdo->prepare("SELECT 
        id_representante, nombre_rep, apellido_rep, id_pais_rep, id_nacionalidad_rep, foto_carnet_rep, 
        sexo_rep, fecha_nac_rep, id_estado_rep, id_municipio_rep, 
        id_parroquia_rep, tel_rep, correo_rep, direccion_rep 
        FROM representantes WHERE cedula_rep = ? LIMIT 1");
        
    $query->execute([$cedula]);
    $rep = $query->fetch(PDO::FETCH_ASSOC);

    if ($rep) {
        // 2. LÓGICA DE GEMELOS/HERMANOS (CONTEO)
        // Buscamos cuántos estudiantes ya están asociados a este ID de representante
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM estudiante WHERE id_representante = ?");
        $stmtCount->execute([$rep['id_representante']]);
        $totalHijos = (int)$stmtCount->fetchColumn();
        
        // El prefijo será: (Cantidad de hijos actuales + 1)
        $proximo_hijo = $totalHijos + 1;

        $foto_nombre = !empty($rep['foto_carnet_rep']) ? $rep['foto_carnet_rep'] : '';

        ob_clean();
        echo json_encode([
            'existe'             => true,
            'id_representante'   => $rep['id_representante'],
            'nombre'             => trim($rep['nombre_rep']),
            'apellido'           => trim($rep['apellido_rep']),
            'id_pais_rep'        => $rep['id_pais_rep'],
            'id_nacionalidad_rep'=> $rep['id_nacionalidad_rep'], 
            'sexo_rep'           => $rep['sexo_rep'],           
            'fecha_nac'          => $rep['fecha_nac_rep'],
            'id_estado_rep'      => $rep['id_estado_rep'],      
            'id_municipio_rep'   => $rep['id_municipio_rep'],
            'id_parroquia_rep'   => $rep['id_parroquia_rep'],
            'tel'                => $rep['tel_rep'],
            'correo'             => $rep['correo_rep'],
            'dir'                => $rep['direccion_rep'],
            'foto'               => $foto_nombre,
            'proximo_hijo'       => $proximo_hijo // <--- VITAL para la Cédula Escolar
        ]);
    } else {
        ob_clean();
        echo json_encode([
            'existe'       => false,
            'proximo_hijo' => 1 // Si no existe, es el primer niño
        ]);
    }
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['existe' => false, 'error' => $e->getMessage()]);
}
exit;