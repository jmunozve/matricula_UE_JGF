<?php
/**
 * guardarestudiante.php - Almacenamiento Temporal en Sesión (Paso 1)
 */
if (session_status() === PHP_SESSION_NONE) session_start();

ob_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // --- PROCESAMIENTO DE ARCHIVOS TEMPORALES ---
    // Subimos los archivos a una carpeta temporal para no dejar basura si no terminan el registro
    function subirTemporal($key) {
        if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION);
            $nombre = "TEMP_" . time() . "_" . uniqid() . "." . $ext;
            $dir = $_SERVER['DOCUMENT_ROOT'] . "/matricula/public/uploads/temp/";
            
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            
            if (move_uploaded_file($_FILES[$key]['tmp_name'], $dir . $nombre)) {
                return "public/uploads/temp/" . $nombre;
            }
        }
        return null;
    }

    // Capturamos las rutas de los archivos subidos
    $foto = subirTemporal('foto_carnet');
    $partida = subirTemporal('pdf_partida');
    $ced_doc = subirTemporal('pdf_cedula');

    // --- RECOGIDA Y ALMACENAMIENTO EN SESIÓN ---
    // Guardamos TODO el $_POST y agregamos las rutas de los archivos
    $datos_estudiante = $_POST;
    $datos_estudiante['foto_ruta'] = $foto;
    $datos_estudiante['partida_ruta'] = $partida;
    $datos_estudiante['cedula_doc_ruta'] = $ced_doc;

    // Esta es la clave: guardamos el objeto completo en la sesión
    $_SESSION['registro_estudiante'] = $datos_estudiante;

    ob_clean();
    echo json_encode([
        "success" => true, 
        "message" => "Datos del estudiante guardados temporalmente. Proceda al paso del representante."
    ]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}