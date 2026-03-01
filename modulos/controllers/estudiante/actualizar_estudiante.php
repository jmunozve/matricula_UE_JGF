<?php
/**
 * Ubicación: F:\xampp\htdocs\matricula\modulos\controllers\estudiante\actualizar_estudiante.php
 */
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';

try {
    $pdo = Conexion::abrir();
    $pdo->beginTransaction();
    
    $id = $_POST['id_estudiante'] ?? null;
    if (!$id) throw new Exception("ID de estudiante no proporcionado");

    // 1. OBTENER DATOS ACTUALES
    $stmt_old = $pdo->prepare("SELECT id_seccion, estatus, foto_carnet, doc_partida, doc_cedula, doc_boleta, doc_sano, doc_vacunas FROM estudiantes WHERE id_estudiante = ?");
    $stmt_old->execute([$id]);
    $actual = $stmt_old->fetch(PDO::FETCH_ASSOC);

    if (!$actual) throw new Exception("Estudiante no encontrado.");

    // 2. VALIDACIÓN DE CUPO (CORREGIDO: capacidad_max)
    $nueva_seccion = !empty($_POST['id_seccion']) ? (int)$_POST['id_seccion'] : null;
    $nuevo_estatus = $_POST['estatus'] ?? $actual['estatus'];

    if ($nueva_seccion) {
        // Solo validamos cupo si cambia de sección o si pasa a estar 'Inscrito'
        if ($nueva_seccion != $actual['id_seccion'] || ($actual['estatus'] !== 'Inscrito' && $nuevo_estatus === 'Inscrito')) {
            
            // Usamos capacidad_max que es el nombre real en tu tabla 'secciones'
            $sql_cupo = "SELECT s.capacidad_max, 
                        (SELECT COUNT(*) FROM estudiantes WHERE id_seccion = s.id_seccion AND estatus = 'Inscrito' AND id_estudiante != ?) as ocupados 
                        FROM secciones s WHERE s.id_seccion = ?";
            
            $stmt_cupo = $pdo->prepare($sql_cupo);
            $stmt_cupo->execute([$id, $nueva_seccion]);
            $cupo = $stmt_cupo->fetch(PDO::FETCH_ASSOC);

            if ($cupo) {
                if ($cupo['ocupados'] >= $cupo['capacidad_max']) {
                    throw new Exception("Sin cupos en la sección destino (Máx: {$cupo['capacidad_max']}).");
                }
            } else {
                throw new Exception("La sección seleccionada no existe o no está activa.");
            }
        }
    }

    // 3. GESTIÓN DE ARCHIVOS
    function subirArchivo($input, $prefijo, $actual_file, $subcarpeta) {
        if (empty($_FILES[$input]['name']) || $_FILES[$input]['error'] !== UPLOAD_ERR_OK) {
            return $actual_file;
        }

        $base_dir = $_SERVER['DOCUMENT_ROOT'] . "/matricula/uploads/" . $subcarpeta . "/";
        if (!is_dir($base_dir)) mkdir($base_dir, 0777, true);

        $ext = strtolower(pathinfo($_FILES[$input]['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($ext, $allowed)) throw new Exception("Extensión no permitida en $input");
        
        $nombre = $prefijo . "_" . uniqid() . "_" . date("Ymd") . "." . $ext;
        if (move_uploaded_file($_FILES[$input]['tmp_name'], $base_dir . $nombre)) {
            // Opcional: podrías eliminar el archivo anterior aquí si existe
            return $nombre;
        }
        return $actual_file;
    }

    $foto    = subirArchivo('foto_carnet', 'FOTO', $actual['foto_carnet'], 'fotos_est');
    $partida = subirArchivo('doc_partida', 'PART', $actual['doc_partida'], 'documentos_est');
    $ced_doc = subirArchivo('doc_cedula', 'CED', $actual['doc_cedula'], 'documentos_est');
    $boleta  = subirArchivo('doc_boleta', 'BOL', $actual['doc_boleta'], 'documentos_est');
    $sano    = subirArchivo('doc_sano', 'SANO', $actual['doc_sano'], 'documentos_est');
    $vacuna  = subirArchivo('doc_vacunas', 'VAC', $actual['doc_vacunas'], 'documentos_est');

    // 4. LÓGICA DE IDENTIDAD Y GEMELOS
    $tipo_doc = $_POST['tipo_doc_es'] ?? 'V';
    $ced_input = trim($_POST['cedula_estudiante'] ?? '');
    $orden_gemelo = (int)($_POST['orden_gemelo'] ?? 0); 
    
    $cedula_es = ($tipo_doc !== 'CE') ? $ced_input : null; 
    $cedula_escolar = ($tipo_doc === 'CE') ? $ced_input : null;

    // 5. UPDATE GENERAL DEL ESTUDIANTE
    $sql = "UPDATE estudiantes SET 
                estatus = ?, foto_carnet = ?, tipo_doc_es = ?, nombre_es = ?, apellido_es = ?, 
                sexo_es = ?, id_pais_es = ?, cedula_es = ?, cedula_escolar = ?, 
                pacto_multiple = ?, tipo_sangre = ?, periodo_escolar = ?, id_seccion = ?, 
                fecha_nacimiento = ?, id_estado_nac = ?, id_mun_nac = ?, id_parroquia_nac = ?,
                id_estado_hab = ?, id_mun_hab = ?, id_parroquia_hab = ?, direccion_detalle = ?, 
                tiene_discapacidad = ?, detalle_discapacidad = ?, doc_partida = ?, 
                doc_cedula = ?, doc_boleta = ?, doc_sano = ?, doc_vacunas = ?,
                id_plantel_procedencia = ?, es_repitiente = ?
            WHERE id_estudiante = ?";

    $params = [
        $nuevo_estatus, $foto, $tipo_doc, 
        mb_strtoupper($_POST['nombre_estudiante'] ?? ''), mb_strtoupper($_POST['apellido_estudiante'] ?? ''),
        $_POST['sexo'] ?? null, 
        !empty($_POST['id_pais_es']) ? (int)$_POST['id_pais_es'] : null, 
        $cedula_es, $cedula_escolar, $orden_gemelo, 
        $_POST['tipo_sangre'] ?? null, $_POST['periodo_escolar'] ?? '2025-2026',
        $nueva_seccion, $_POST['fecha_nacimiento'] ?? null,
        !empty($_POST['id_estado_nac']) ? (int)$_POST['id_estado_nac'] : null,
        !empty($_POST['id_mun_nac']) ? (int)$_POST['id_mun_nac'] : null,
        !empty($_POST['id_parroquia_nac']) ? (int)$_POST['id_parroquia_nac'] : null,
        !empty($_POST['id_estado_hab']) ? (int)$_POST['id_estado_hab'] : null,
        !empty($_POST['id_mun_hab']) ? (int)$_POST['id_mun_hab'] : null,
        !empty($_POST['id_parroquia_hab']) ? (int)$_POST['id_parroquia_hab'] : null,
        $_POST['direccion_detalle'] ?? null, $_POST['tiene_discapacidad'] ?? 'No', $_POST['detalle_discapacidad'] ?? null,
        $partida, $ced_doc, $boleta, $sano, $vacuna,
        !empty($_POST['id_plantel_procedencia']) ? (int)$_POST['id_plantel_procedencia'] : 1, 
        (isset($_POST['es_repitiente']) ? (int)$_POST['es_repitiente'] : 0),
        $id
    ];

    $pdo->prepare($sql)->execute($params);

    // 6. FAMILIARES (ELIMINACIÓN)
    if (!empty($_POST['eliminar_familiares']) && is_array($_POST['eliminar_familiares'])) {
        $ids_del = $_POST['eliminar_familiares'];
        $placeholders = implode(',', array_fill(0, count($ids_del), '?'));
        $pdo->prepare("DELETE FROM estudiante_familiares WHERE id_est_fam IN ($placeholders)")->execute($ids_del);
    }

    // 7. FAMILIARES (UPSERT)
    if (isset($_POST['id_est_fam']) && is_array($_POST['id_est_fam'])) {
        // No reseteamos es_principal aquí porque el usuario puede estar editando varios familiares a la vez
        // Solo nos aseguramos de procesar cada uno.
        
        $stmt_ins = $pdo->prepare("INSERT INTO estudiante_familiares (id_estudiante, id_parentesco, cedula_fam, nombres_fam, telefono_fam, correo_fam, es_principal) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_upd = $pdo->prepare("UPDATE estudiante_familiares SET id_parentesco = ?, cedula_fam = ?, nombres_fam = ?, telefono_fam = ?, correo_fam = ?, es_principal = ? WHERE id_est_fam = ?");

        foreach ($_POST['id_est_fam'] as $index => $fam_id) {
            $nom = mb_strtoupper(trim($_POST['nombres_fam'][$index] ?? ''));
            if (empty($nom)) continue; 

            $p_id = !empty($_POST['id_parentesco_fam'][$index]) ? (int)$_POST['id_parentesco_fam'][$index] : null;
            $es_p = (isset($_POST['es_principal_fam'][$index]) && $_POST['es_principal_fam'][$index] == "1") ? 1 : 0;

            $vals = [
                $p_id, 
                trim($_POST['cedula_fam'][$index] ?? ''), 
                $nom, 
                trim($_POST['telefono_fam'][$index] ?? ''), 
                trim($_POST['correo_fam'][$index] ?? ''),
                $es_p
            ];

            if ($fam_id === "NUEVO") {
                $ins_vals = array_merge([$id], $vals);
                $stmt_ins->execute($ins_vals);
            } else {
                $vals[] = (int)$fam_id;
                $stmt_upd->execute($vals);
            }
        }
    }

    $pdo->commit();
    echo json_encode(["status" => "success", "message" => "¡Ficha del estudiante actualizada correctamente!"]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}