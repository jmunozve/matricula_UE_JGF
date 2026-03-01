<?php
require_once "../../../includes/db.php";
// Asegúrate de tener la librería FPDF en tu carpeta de vendor o includes
require_once "../../../includes/fpdf/fpdf.php"; 

class PDF extends FPDF {
    function Header() {
        // Logo del Plantel (si tienes uno)
        // $this->Image('../../../assets/img/logo.png', 10, 8, 33);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 5, utf8_decode('REPÚBLICA BOLIVARIANA DE VENEZUELA'), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, utf8_decode('MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('U.E. "NOMBRE DE TU INSTITUCIÓN"'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-30);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// 1. Obtener ID y validar
$id_estudiante = $_GET['id'] ?? null;
if (!$id_estudiante) die("ID de estudiante no proporcionado.");

try {
    $pdo = Conexion::abrir();
    
    // Consulta completa: Datos del Estudiante + El último retiro registrado
    $sql = "SELECT e.*, r.fecha_retiro, r.observaciones, m.nombre_motivo,
                   g.nombre_grado, s.letra, n.nombre_nivel
            FROM estudiante e
            INNER JOIN retiros_estudiantes r ON e.id_estudiante = r.id_estudiante
            INNER JOIN motivos_retiro m ON r.id_motivo = m.id_motivo
            LEFT JOIN secciones s ON e.id_seccion = s.id_seccion -- Opcional si ya fue liberado
            LEFT JOIN grados g ON s.id_grado = g.id_grado
            LEFT JOIN niveles_estudio n ON g.id_nivel = n.id_nivel
            WHERE e.id_estudiante = ? 
            ORDER BY r.id_retiro DESC LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_estudiante]);
    $d = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$d) die("No se encontró registro de retiro para este estudiante.");

    // 2. Generar PDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, utf8_decode('CONSTANCIA DE RETIRO'), 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 11);
    $texto = "Quien suscribe, la Dirección de la U.E. [NOMBRE INSTITUCIÓN], hace constar por medio de la presente que el (la) estudiante: ";
    $pdf->MultiCell(0, 7, utf8_decode($texto));
    
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, utf8_decode(strtoupper($d['apellido'] . ", " . $d['nombre'])), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 7, utf8_decode("Cédula / Escolar: " . ($d['cedula_escolar'] ?: $d['cedula'])), 0, 1, 'C');
    
    $pdf->Ln(5);
    $cuerpo = "Cursaba estudios en el nivel de " . $d['nombre_nivel'] . ", grado " . $d['nombre_grado'] . ". El retiro se hace efectivo en fecha " . date('d/m/Y', strtotime($d['fecha_retiro'])) . ", bajo el motivo de: " . strtoupper($d['nombre_motivo']) . ".";
    $pdf->MultiCell(0, 7, utf8_decode($cuerpo));

    if(!empty($d['observaciones'])){
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->MultiCell(0, 5, utf8_decode("Observaciones: " . $d['observaciones']));
    }

    $pdf->Ln(20);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 7, utf8_decode("Constancia que se expide a petición de la parte interesada en la fecha: " . date('d/m/Y')), 0, 1, 'L');

    // Firmas
    $pdf->Ln(30);
    $pdf->Cell(90, 0, '', 'T', 0, 'C');
    $pdf->Cell(10, 0, '', 0, 0, 'C');
    $pdf->Cell(90, 0, '', 'T', 1, 'C');
    
    $pdf->Cell(90, 7, utf8_decode('Sello del Plantel'), 0, 0, 'C');
    $pdf->Cell(10, 7, '', 0, 0, 'C');
    $pdf->Cell(90, 7, utf8_decode('Firma Autorizada'), 0, 1, 'C');

    $pdf->Output('I', 'Retiro_' . $d['cedula'] . '.pdf');

} catch (Exception $e) {
    die("Error al generar reporte: " . $e->getMessage());
}