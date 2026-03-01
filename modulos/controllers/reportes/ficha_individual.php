<?php
/**
 * FICHA DE INSCRIPCIÓN - UNIDAD EDUCATIVA "GIL FORTOUL"
 * Versión optimizada utilizando la vista 'vista_estudiantes_completa'
 */

if (ob_get_level()) ob_end_clean();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once('../../../includes/auth.php'); 
require_once('../../../libs/fpdf/fpdf.php');
require_once('../../../includes/db.php');

function limpiar($texto) {
    return mb_convert_encoding($texto ?? '', 'ISO-8859-1', 'UTF-8');
}

function mostrarAlerta($mensaje) {
    echo "<div style='font-family:Arial; text-align:center; padding:50px;'>
            <div style='display:inline-block; border:1px solid #f5c6cb; background:#f8d7da; color:#721c24; padding:20px; border-radius:5px;'>
                <h2>⚠️ Error de Generación</h2>
                <p>$mensaje</p>
                <button onclick='window.close()' style='background:#721c24; color:white; border:none; padding:10px 20px; border-radius:3px; cursor:pointer;'>Cerrar Ventana</button>
            </div>
          </div>";
    exit;
}

$id_raw = isset($_GET['id']) ? trim($_GET['id']) : '';
if (empty($id_raw)) mostrarAlerta("No se recibió ningún ID.");
$id_buscar = str_replace(['.', '-', ' '], '', $id_raw);

class FICHA extends FPDF {
    function Code128($x, $y, $code, $w, $h) {
        $this->SetLineWidth(0.2); $this->Rect($x, $y, $w, $h);
        $this->SetXY($x, $y + $h + 0.5); $this->SetFont('Arial', '', 7);
        $this->Cell($w, 3, $code, 0, 0, 'C');
        $this->SetFillColor(0,0,0);
        $len = strlen($code);
        for($i=0; $i<$len; $i++) {
            $width = ($i % 2 == 0) ? 0.6 : 0.2; 
            $this->Rect($x + 2 + ($i*1.5), $y + 2, $width, $h - 4, 'F');
        }
    }
    
    function Header() {
        $ruta_logo = '../../../public/assets/img/logo.png'; 
        if (file_exists($ruta_logo)) {
            $img_info = @getimagesize($ruta_logo);
            if ($img_info !== false && $img_info['mime'] === 'image/png') {
                $this->Image($ruta_logo, 10, 10, 22);
            }
        }
        
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, limpiar('UNIDAD EDUCATIVA "GIL FORTOUL"'), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, limpiar('PLANILLA DE INSCRIPCIÓN OFICIAL - AÑO ESCOLAR 2025-2026'), 0, 1, 'C');
        $this->Ln(15);
        $this->Line(10, 38, 205, 38);
    }
}

try {
    $pdo = Conexion::abrir();
    
    // 1. Verificación inicial usando la vista para obtener el id_representante y pacto_multiple
    $stmtCheck = $pdo->prepare("SELECT id_estudiante, id_representante, pacto_multiple FROM vista_estudiantes_completa WHERE id_estudiante = ? OR cedula_es = ? OR cedula_escolar = ? LIMIT 1");
    $stmtCheck->execute([$id_buscar, $id_buscar, $id_buscar]);
    $res = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$res) mostrarAlerta("No se encontró al estudiante.");

    // 2. Consulta principal uniendo la VISTA con la tabla REPRESENTANTES
    $sqlBase = "SELECT v.*, 
                r.nombre_rep, r.apellido_rep, r.cedula_rep, r.tel_rep, r.correo_rep, r.direccion_detalle_rep, r.parentesco_rep
                FROM vista_estudiantes_completa v
                INNER JOIN representantes r ON v.id_representante = r.id_representante";

    // Aplicar lógica de pacto_multiple (gemelos)
    if ($res['pacto_multiple'] == 1) {
        $stmt = $pdo->prepare($sqlBase . " WHERE v.id_representante = ? ORDER BY v.cedula_escolar ASC");
        $stmt->execute([$res['id_representante']]);
    } else {
        $stmt = $pdo->prepare($sqlBase . " WHERE v.id_estudiante = ?");
        $stmt->execute([$res['id_estudiante']]);
    }

    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdf = new FICHA('P', 'mm', 'Letter');
    $pdf->SetAutoPageBreak(true, 15);

    foreach ($estudiantes as $data) {
        $pdf->AddPage();
        
        // El ID para el código de barras prioritario
        $id_v = !empty($data['cedula_es']) ? $data['cedula_es'] : $data['cedula_escolar'];
        $pdf->Code128(165, 12, $id_v, 35, 12);

        // SECCIÓN 1: INFORMACIÓN ACADÉMICA (Datos provenientes de la vista)
        $pdf->SetY(45);
        $pdf->SetFont('Arial', 'B', 10); $pdf->SetFillColor(235, 235, 235);
        $pdf->Cell(196, 7, limpiar('1. INFORMACIÓN ACADÉMICA'), 1, 1, 'L', 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(65, 7, limpiar('Nivel: ' . ($data['nombre_nivel'] ?? 'N/A')), 1);
        $pdf->Cell(65, 7, limpiar('Grado: ' . ($data['nombre_grado'] ?? 'N/A')), 1);
        $pdf->Cell(33, 7, limpiar('Sección: ' . ($data['nombre_seccion'] ?? 'N/A')), 1);
        $pdf->Cell(33, 7, limpiar('Turno: ' . ($data['turno'] ?? 'N/A')), 1, 1);
        
        // SECCIÓN 2: DATOS DEL ESTUDIANTE
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(196, 7, limpiar('2. DATOS DEL ESTUDIANTE'), 1, 1, 'L', 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(130, 7, limpiar('Apellidos y Nombres: ' . $data['apellido_es'] . ' ' . $data['nombre_es']), 1);
        $pdf->Cell(66, 7, limpiar('C.I./Escolar: ' . $id_v), 1, 1);
        $pdf->Cell(98, 7, limpiar('Fecha de Nacimiento: ' . $data['fecha_nacimiento']), 1);
        $pdf->Cell(98, 7, limpiar('Sexo: ' . ($data['sexo_es'] == 1 ? 'Masculino' : 'Femenino')), 1, 1);

        // SECCIÓN 3: DATOS DEL REPRESENTANTE
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(196, 7, limpiar('3. DATOS DEL REPRESENTANTE'), 1, 1, 'L', 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(130, 7, limpiar('Nombre: ' . $data['nombre_rep'] . ' ' . $data['apellido_rep']), 1);
        $pdf->Cell(66, 7, limpiar('C.I.: ' . $data['cedula_rep']), 1, 1);
        $pdf->Cell(98, 7, limpiar('Teléfono: ' . $data['tel_rep']), 1);
        $pdf->Cell(98, 7, limpiar('Correo: ' . $data['correo_rep']), 1, 1);
        $pdf->Cell(196, 7, limpiar('Dirección: ' . $data['direccion_detalle_rep']), 1, 1);
        
        // FIRMAS
        $pdf->SetY(-45);
        $pdf->Cell(98, 5, '__________________________', 0, 0, 'C');
        $pdf->Cell(98, 5, '__________________________', 0, 1, 'C');
        $pdf->Cell(98, 5, 'Firma del Representante', 0, 0, 'C');
        $pdf->Cell(98, 5, 'Sello y Firma Directiva', 0, 1, 'C');
    }

    $pdf->Output('I', "Ficha_" . $id_buscar . ".pdf");

} catch (Exception $e) {
    mostrarAlerta("Error técnico: " . $e->getMessage());
}