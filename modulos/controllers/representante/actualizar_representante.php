<?php
// F:\xampp\htdocs\matricula\modulos\controllers\representante\actualizar_representante.php
require_once dirname(__DIR__, 3) . '/includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $pdo = Conexion::abrir();

        // 1. Validaciones Iniciales
        $id_representante = $_POST['id_representante'] ?? null;
        if (!$id_representante) throw new Exception("ID de representante ausente.");

        $cedula   = trim($_POST['cedula_rep'] ?? '');
        $nombre   = strtoupper(trim($_POST['nombre_rep'] ?? ''));
        $apellido = strtoupper(trim($_POST['apellido_rep'] ?? ''));

        if (empty($cedula) || empty($nombre)) throw new Exception("Nombre y Cédula son obligatorios.");

        // Verificar duplicados (excluyendo al representante actual)
        $stmtCheck = $pdo->prepare("SELECT id_representante FROM representantes WHERE cedula_rep = ? AND id_representante <> ?");
        $stmtCheck->execute([$cedula, $id_representante]);
        if ($stmtCheck->fetch()) {
            throw new Exception("La cédula $cedula ya pertenece a otro registro.");
        }

        // 2. Rutas y Datos Previos
        $ruta_base_foto = "../../../uploads/fotos_reps/";
        $ruta_base_pdf  = "../../../uploads/documentos_reps/";

        $stmtOld = $pdo->prepare("SELECT foto_carnet_rep, pdf_cedula_rep FROM representantes WHERE id_representante = ?");
        $stmtOld->execute([$id_representante]);
        $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

        // 3. Procesamiento de Archivos (Foto y PDF)
        $foto_sql = "";
        $pdf_sql = "";
        $extra_params = [];

        // Lógica FOTO (name="foto_carnet")
        if (!empty($_FILES['foto_carnet']['name'])) {
            $ext = strtolower(pathinfo($_FILES['foto_carnet']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) throw new Exception("Formato de imagen no permitido.");
            
            $foto_nombre = "FOTO_" . $cedula . "_" . time() . "." . $ext;
            
            if (move_uploaded_file($_FILES['foto_carnet']['tmp_name'], $ruta_base_foto . $foto_nombre)) {
                if (!empty($oldData['foto_carnet_rep']) && file_exists($ruta_base_foto . $oldData['foto_carnet_rep'])) {
                    unlink($ruta_base_foto . $oldData['foto_carnet_rep']);
                }
                $foto_sql = ", foto_carnet_rep = ?";
                $extra_params[] = $foto_nombre;
            }
        }

        // Lógica PDF (name="pdf_cedula_rep")
        if (!empty($_FILES['pdf_cedula_rep']['name'])) {
            $ext = strtolower(pathinfo($_FILES['pdf_cedula_rep']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'jpg', 'png'])) throw new Exception("Formato de documento no permitido.");
            
            $pdf_nombre = "DOC_" . $cedula . "_" . time() . "." . $ext;
            
            if (move_uploaded_file($_FILES['pdf_cedula_rep']['tmp_name'], $ruta_base_pdf . $pdf_nombre)) {
                if (!empty($oldData['pdf_cedula_rep']) && file_exists($ruta_base_pdf . $oldData['pdf_cedula_rep'])) {
                    unlink($ruta_base_pdf . $oldData['pdf_cedula_rep']);
                }
                $pdf_sql = ", pdf_cedula_rep = ?";
                $extra_params[] = $pdf_nombre;
            }
        }

        // 4. Construcción Dinámica del UPDATE
        $sql = "UPDATE representantes SET 
                    nombre_rep = ?, apellido_rep = ?, sexo_rep = ?, tipo_doc_rep = ?, 
                    id_nacionalidad_rep = ?, id_pais_rep = ?, tel_rep = ?, correo_rep = ?, 
                    parentesco_rep = ?, cedula_rep = ?, fecha_nac_rep = ?, id_estado_rep = ?, 
                    id_municipio_rep = ?, id_parroquia_rep = ?, direccion_detalle_rep = ?
                    $foto_sql 
                    $pdf_sql
                WHERE id_representante = ?";
        
        $params = [
            $nombre, $apellido, 
            $_POST['sexo_rep'] ?? null, 
            $_POST['tipo_doc_rep'] ?? 'V',
            $_POST['id_nacionalidad_rep'] ?? 1, 
            $_POST['id_pais_rep'] ?? null, 
            $_POST['tel_rep'] ?? '', 
            $_POST['correo_rep'] ?? '', 
            $_POST['parentesco_rep'] ?? null, 
            $cedula,
            $_POST['fecha_nac_rep'] ?? null, 
            $_POST['id_estado_rep'] ?? null, 
            $_POST['id_municipio_rep'] ?? null, 
            $_POST['id_parroquia_rep'] ?? null, 
            $_POST['direccion_detalle_rep'] ?? ''
        ];

        // Unir parámetros básicos + archivos + el ID del WHERE
        $final_params = array_merge($params, $extra_params, [$id_representante]);
        
        $pdo->prepare($sql)->execute($final_params);

        echo json_encode(["status" => "success", "message" => "Expediente actualizado correctamente."]);

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}