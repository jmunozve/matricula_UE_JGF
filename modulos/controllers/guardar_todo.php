<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once dirname(__DIR__, 2) . '/includes/db.php'; 

// Función ajustada a tu estructura de carpetas (fotos/documentos)
function procesarCarga($file, $destinoPrincipal, $subCarpeta) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
    
    // Ruta basada en tu imagen de carpetas: uploads/estudiantes/fotos/ o uploads/representantes/docs/
    $dir = "../../uploads/" . $destinoPrincipal . "/" . $subCarpeta . "/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombreArchivo = $subCarpeta . "_" . time() . "_" . uniqid() . "." . $ext;
    
    if (move_uploaded_file($file['tmp_name'], $dir . $nombreArchivo)) {
        return $nombreArchivo;
    }
    return null;
}

try {
    $pdo = Conexion::abrir();
    $pdo->beginTransaction();

    // --- 1. SUBIDA DE ARCHIVOS (Sincronizado con registro.php) ---
    // Estudiante
    $foto_est   = procesarCarga($_FILES['foto_carnet'] ?? null, 'estudiantes', 'fotos');
    $pdf_ced_est = procesarCarga($_FILES['doc_cedula'] ?? null, 'estudiantes', 'documentos');
    $pdf_partida = procesarCarga($_FILES['doc_partida'] ?? null, 'estudiantes', 'documentos');
    
    // Representante
    $foto_rep   = procesarCarga($_FILES['foto_rep'] ?? null, 'representantes', 'fotos');
    $pdf_ced_rep = procesarCarga($_FILES['pdf_cedula_rep'] ?? null, 'representantes', 'docs');

    // --- 2. PROCESAR REPRESENTANTE ---
    $cedula_rep = preg_replace('/[^0-9]/', '', $_POST['cedula_rep'] ?? '');
    
    $stmtRep = $pdo->prepare("SELECT id_representante FROM representantes WHERE cedula_rep = ? LIMIT 1");
    $stmtRep->execute([$cedula_rep]);
    $rep_db = $stmtRep->fetch();

    if ($rep_db) {
        $id_rep = $rep_db['id_representante'];
    } else {
        $sqlInsRep = "INSERT INTO representantes (
            nombre, apellido, sexo_rep, tipo_doc_rep, parentesco_rep, 
            cedula_rep, fecha_nac_rep, correo_rep, contacto,
            id_estado_rep, id_municipio_rep, id_parroquia_rep, direccion_rep,
            foto_carnet_rep, pdf_cedula_rep, discapacidad_rep
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        
        $pdo->prepare($sqlInsRep)->execute([
            strtoupper($_POST['nombre_rep'] ?? ''), strtoupper($_POST['apellido_rep'] ?? ''),
            $_POST['sexo_rep'] ?? 'Femenino', $_POST['tipo_doc_rep'] ?? 'V',
            $_POST['parentesco_rep'] ?? '', $cedula_rep, $_POST['fecha_nac_rep'] ?: null,
            $_POST['correo_rep'] ?? null, $_POST['telefono_rep'] ?? null,
            $_POST['id_estado_rep'] ?: null, $_POST['id_mun_rep'] ?: null,
            $_POST['id_parroquia_rep'] ?: null, strtoupper($_POST['direccion_rep'] ?? ''),
            $foto_rep, $pdf_ced_rep, $_POST['tiene_discapacidad_rep'] ?? 'No'
        ]);
        $id_rep = $pdo->lastInsertId();
    }

    // --- 3. LÓGICA DE CÉDULA ESCOLAR (Sincronizado con JS generarEscolar) ---
    $tiene_ci = strtolower($_POST['tiene_ci'] ?? 'no'); 
    $cedula_input = trim($_POST['cedula_estudiante'] ?? '');
    $tipo_doc_es = $_POST['tipo_doc_es'] ?? 'V';
    
    if ($tiene_ci === 'si') {
        if (empty($cedula_input)) throw new Exception("Seleccionó cédula '{$tipo_doc_es}' pero el campo está vacío.");
        $cedula_final = $cedula_input;
        $cedula_escolar_final = null; 
    } else {
        $tipo_doc_es = 'CE';
        $prefijo = $_POST['orden_gemelo'] ?? '1'; 
        $anio = date('y', strtotime($_POST['fecha_nacimiento'] ?? date('Y-m-d')));
        $cedula_escolar_final = $prefijo . $anio . str_pad($cedula_rep, 8, '0', STR_PAD_LEFT);
        $cedula_final = $cedula_escolar_final; 
    }

    // --- 4. INSERTAR ESTUDIANTE ---
    $sqlEst = "INSERT INTO estudiante (
        id_representante, estatus, tipo_doc_es, nombre, apellido, sexo,
        nacionalidad, tipo_sangre, cedula, cedula_escolar, fecha_nacimiento,
        id_estado_nac, id_mun_nac, id_parroquia_nac,
        id_estado_hab, id_mun_hab, id_parroquia_hab,
        direccion_detalle, fk_grado, id_seccion, periodo_escolar,
        nivel_estudio, modalidad, tiene_discapacidad, detalle_discapacidad,
        foto_carnet, doc_cedula, doc_partida
    ) VALUES (?, 'Inscrito', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $pdo->prepare($sqlEst)->execute([
        $id_rep, $tipo_doc_es, 
        strtoupper($_POST['nombre_estudiante'] ?? ''), strtoupper($_POST['apellido_estudiante'] ?? ''),
        $_POST['sexo'] ?? null, $_POST['nacionalidad'] ?? 'Venezolana', $_POST['tipo_sangre'] ?? null,
        $cedula_final, $cedula_escolar_final, $_POST['fecha_nacimiento'] ?: null,
        $_POST['id_estado_nac'] ?: null, $_POST['id_mun_nac'] ?: null, $_POST['id_parroquia_nac'] ?: null,
        $_POST['id_estado_hab'] ?: null, $_POST['id_mun_hab'] ?: null, $_POST['id_parroquia_hab'] ?: null,
        strtoupper($_POST['direccion_detalle'] ?? ''), $_POST['id_grado'] ?? 0, $_POST['id_seccion'] ?: null,
        "2025-2026", $_POST['id_nivel'] ?? null, $_POST['id_modalidad'] ?? null,
        $_POST['discapacidad'] ?? 'No', $_POST['discapacidad_detalle'] ?? null,
        $foto_est, $pdf_ced_est, $pdf_partida
    ]);

    $pdo->commit();
    ob_clean();
    echo json_encode(["success" => true, "mensaje" => "¡Registro y archivos guardados correctamente!"]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    ob_clean();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}