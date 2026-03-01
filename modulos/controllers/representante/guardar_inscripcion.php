<?php

/**
 * guardar_inscripcion.php - Registro Unificado (Representante, Estudiante y Familiares)
 * TOTALMENTE CORREGIDO: Redefinición de funciones, lógica de gemelos y estatus.
 */

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once "../../../includes/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

// Definición de función fuera del bloque try para evitar errores de re-declaración
if (!function_exists('subirArchivo')) {
    function subirArchivo($fileKey, $folder)
    {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) return null;
        $extension = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
        $nuevoNombre = uniqid("DOC_") . "_" . date("Ymd") . "." . $extension;
        $rutaBase = "../../../uploads/" . $folder;
        if (!is_dir($rutaBase)) mkdir($rutaBase, 0777, true);
        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $rutaBase . "/" . $nuevoNombre)) return $nuevoNombre;
        return null;
    }
}

try {
    $pdo = Conexion::abrir();

    // 1. CAPTURA Y LIMPIEZA DE DATOS
    $cedula_rep = trim($_POST['cedula_rep'] ?? '');
    $fecha_est_str = $_POST['fecha_nacimiento'] ?? '';
    $id_parentesco_rep = $_POST['parentesco_rep'] ?? '';
    $id_seccion = !empty($_POST['id_seccion']) ? intval($_POST['id_seccion']) : null;
    // --- IMPORTANTE: Definir la variable con un respaldo para evitar Warnings ---
    $periodo_escolar = !empty($_POST['periodo_escolar']) ? $_POST['periodo_escolar'] : '2025-2026';
    $tiene_ci = $_POST['tiene_ci'] ?? 'SI';
    $input_cedula = trim($_POST['cedula_es'] ?? '');
    $cedula_es_final = ($tiene_ci === 'SI') ? $input_cedula : 'S/C-' . $input_cedula;
    $cedula_escolar_final = ($tiene_ci === 'NO') ? $input_cedula : null;

    if (empty($cedula_rep) || empty($fecha_est_str)) {
        throw new Exception("Faltan datos obligatorios (Cédula Representante o Fecha de Nacimiento).");
    }

    // 2. VALIDACIÓN DE DUPLICADOS (CEDULA ESCOLAR / GEMELOS)
    if ($cedula_escolar_final) {
        $stmtCheck = $pdo->prepare("SELECT id_estudiante FROM estudiantes WHERE cedula_escolar = ?");
        $stmtCheck->execute([$cedula_escolar_final]);
        if ($stmtCheck->fetch()) {
            throw new Exception("La cédula escolar {$cedula_escolar_final} ya existe. Si es un gemelo, verifique el contador (1, 2, 3) en el formulario.");
        }
    }

    $pdo->beginTransaction();

    // 3. PROCESAR ARCHIVOS
    $foto_rep    = subirArchivo('foto_carnet_rep', 'fotos_reps');
    $pdf_ced_rep = subirArchivo('pdf_cedula_rep', 'documentos_reps');
    $foto_est    = subirArchivo('foto_carnet', 'fotos_est');
    $doc_partida = subirArchivo('doc_partida', 'documentos_est');
    $doc_cedula  = subirArchivo('doc_cedula', 'documentos_est');
    $doc_boleta  = subirArchivo('doc_boleta', 'documentos_est');
    $doc_sano    = subirArchivo('doc_sano', 'documentos_est');
    $doc_vacunas = subirArchivo('doc_vacunas', 'documentos_est');

    // 4. GESTIÓN DEL REPRESENTANTE (UPDATE O INSERT)
    $stmtBusca = $pdo->prepare("SELECT id_representante, foto_carnet_rep, pdf_cedula_rep FROM representantes WHERE cedula_rep = ?");
    $stmtBusca->execute([$cedula_rep]);
    $repExistente = $stmtBusca->fetch(PDO::FETCH_ASSOC);

    if ($repExistente) {
        $id_representante = $repExistente['id_representante'];
        $foto_rep_final = $foto_rep ?? $repExistente['foto_carnet_rep'];
        $pdf_ced_rep_final = $pdf_ced_rep ?? $repExistente['pdf_cedula_rep'];

        $sqlUpd = "UPDATE representantes SET 
                    nombre_rep=?, apellido_rep=?, sexo_rep=?, tipo_doc_rep=?, id_pais_rep=?, 
                    tel_rep=?, correo_rep=?, parentesco_rep=?, fecha_nac_rep=?, 
                    id_estado_rep=?, id_municipio_rep=?, id_parroquia_rep=?, 
                    direccion_detalle_rep=?, foto_carnet_rep=?, pdf_cedula_rep=?
                   WHERE id_representante=?";
        $pdo->prepare($sqlUpd)->execute([
            mb_strtoupper($_POST['nombre_rep']),
            mb_strtoupper($_POST['apellido_rep']),
            $_POST['sexo_rep'],
            $_POST['tipo_doc_rep'],
            ($_POST['id_pais_rep'] ?: 232),
            $_POST['tel_rep'],
            $_POST['correo_rep'],
            $id_parentesco_rep,
            $_POST['fecha_nac_rep'],
            $_POST['id_estado_rep'],
            $_POST['id_municipio_rep'],
            $_POST['id_parroquia_rep'],
            $_POST['direccion_detalle_rep'],
            $foto_rep_final,
            $pdf_ced_rep_final,
            $id_representante
        ]);
    } else {
        $sqlInsRep = "INSERT INTO representantes (
            nombre_rep, apellido_rep, sexo_rep, tipo_doc_rep, id_pais_rep, 
            tel_rep, correo_rep, parentesco_rep, cedula_rep, fecha_nac_rep, 
            id_estado_rep, id_municipio_rep, id_parroquia_rep, direccion_detalle_rep, 
            foto_carnet_rep, pdf_cedula_rep
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $pdo->prepare($sqlInsRep)->execute([
            mb_strtoupper($_POST['nombre_rep']),
            mb_strtoupper($_POST['apellido_rep']),
            $_POST['sexo_rep'],
            $_POST['tipo_doc_rep'],
            ($_POST['id_pais_rep'] ?: 232),
            $_POST['tel_rep'],
            $_POST['correo_rep'],
            $id_parentesco_rep,
            $cedula_rep,
            $_POST['fecha_nac_rep'],
            $_POST['id_estado_rep'],
            $_POST['id_municipio_rep'],
            $_POST['id_parroquia_rep'],
            $_POST['direccion_detalle_rep'],
            $foto_rep,
            $pdf_ced_rep
        ]);
        $id_representante = $pdo->lastInsertId();
    }


    // --- 5. REGISTRO DEL ESTUDIANTE ---
    $discapacidades_str = isset($_POST['discapacidades']) ? implode(", ", $_POST['discapacidades']) : "";
    if (!empty($_POST['detalle_discapacidad_obs'])) $discapacidades_str .= " | Obs: " . $_POST['detalle_discapacidad_obs'];

    $pacto_val = (isset($_POST['pacto_multiple']) && in_array($_POST['pacto_multiple'], ['1', 'SI'])) ? 1 : 0;

    $sqlEst = "INSERT INTO estudiantes (
        id_representante, foto_carnet, nombre_es, apellido_es, sexo_es, 
        id_pais_es, id_nacionalidad, cedula_es, cedula_escolar, pacto_multiple, tipo_sangre, 
        id_seccion, fecha_nacimiento, id_estado_nac, id_mun_nac, id_parroquia_nac, 
        id_estado_hab, id_mun_hab, id_parroquia_hab, direccion_detalle, 
        tiene_discapacidad, detalle_discapacidad, doc_partida, doc_cedula, 
        doc_boleta, doc_sano, doc_vacunas, es_repitiente, registrado_por, estatus,
        periodo_escolar
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'Pre-inscrito', ?)";

    $pdo->prepare($sqlEst)->execute([
        $id_representante,
        $foto_est,
        mb_strtoupper($_POST['nombre_es']),
        mb_strtoupper($_POST['apellido_es']),
        $_POST['sexo_es'],
        ($_POST['id_pais_es'] ?: 232),
        $_POST['nacionalidad_es'],
        $cedula_es_final,
        $cedula_escolar_final,
        $pacto_val,
        $_POST['tipo_sangre'],
        $id_seccion,
        $_POST['fecha_nacimiento'],
        $_POST['id_estado_nac'],
        $_POST['id_mun_nac'],
        $_POST['id_parroquia_nac'],
        $_POST['id_estado_hab'], // Eliminada la asignación interna que tenías aquí
        $_POST['id_mun_hab'],
        $_POST['id_parroquia_hab'],
        $_POST['direccion_detalle'],
        ($_POST['tiene_discapacidad'] === 'Si' ? 'Si' : 'No'),
        $discapacidades_str,
        $doc_partida,
        $doc_cedula,
        $doc_boleta,
        $doc_sano,
        $doc_vacunas,
        (isset($_POST['es_repitiente']) ? 1 : 0),
        ($_SESSION['usuario_nombre'] ?? 'Sistema'),
        $periodo_escolar // <--- Valor limpio
    ]);

    $id_estudiante = $pdo->lastInsertId();

    // 6. SINCRONIZACIÓN EN TABLA estudiante_familiares
    // Representante como familiar principal
    $sqlFamPrincipal = "INSERT INTO estudiante_familiares (id_estudiante, id_parentesco, cedula_fam, nombres_fam, telefono_fam, correo_fam, es_principal) 
                        VALUES (?, ?, ?, ?, ?, ?, 1)";
    $pdo->prepare($sqlFamPrincipal)->execute([
        $id_estudiante,
        $id_parentesco_rep,
        $cedula_rep,
        mb_strtoupper($_POST['nombre_rep'] . " " . $_POST['apellido_rep']),
        $_POST['tel_rep'],
        $_POST['correo_rep']
    ]);

    // Familiares adicionales
    if (isset($_POST['f_cedula']) && is_array($_POST['f_cedula'])) {
        $stmtFam = $pdo->prepare("INSERT INTO estudiante_familiares (id_estudiante, id_parentesco, cedula_fam, nombres_fam, telefono_fam, correo_fam, es_principal) VALUES (?, ?, ?, ?, ?, ?, 0)");
        foreach ($_POST['f_cedula'] as $index => $ced) {
            $ced_limpia = trim($ced);
            if (!empty($ced_limpia) && $ced_limpia != $cedula_rep) {
                $stmtFam->execute([
                    $id_estudiante,
                    ($_POST['f_parentesco'][$index] ?: null),
                    $ced_limpia,
                    mb_strtoupper(trim($_POST['f_nombre'][$index])),
                    ($_POST['f_telefono'][$index] ?? null),
                    ($_POST['f_correo'][$index] ?? null)
                ]);
            }
        }
    }

    // 7. ACTUALIZACIÓN DE GEMELOS (Marcado retroactivo)
    if ($pacto_val == 1) {
        $sqlMorocho = "UPDATE estudiantes SET pacto_multiple = 1 WHERE id_representante = ? AND fecha_nacimiento = ?";
        $pdo->prepare($sqlMorocho)->execute([$id_representante, $_POST['fecha_nacimiento']]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => '¡Registro completado con éxito! Estudiante ID: ' . $id_estudiante]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
