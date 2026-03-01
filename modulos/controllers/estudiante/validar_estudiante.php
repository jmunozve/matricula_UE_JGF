<?php
/**
 * validar_estudiante.php - Lógica de Prefijos, Gemelos y Periodo Escolar
 * CORREGIDO: Eliminada columna 'estatus' inexistente
 */
header('Content-Type: application/json; charset=utf-8');
require_once "../../../includes/db.php";

try {
    $pdo = Conexion::abrir();
    $cedula_est = $_GET['cedula_est'] ?? null;
    $cedula_rep = $_GET['cedula_rep'] ?? null;
    $fecha_nac  = $_GET['fecha_nac'] ?? null;
    $periodo    = $_GET['periodo'] ?? null;

    // Si el periodo no llegó por la URL, lo buscamos sin usar la columna 'estatus'
    if (!$periodo || $periodo == "null" || $periodo == "undefined") {
        // Simplemente tomamos el primer registro de la tabla planteles
        $stmtP = $pdo->query("SELECT periodo_escolar FROM planteles LIMIT 1");
        $pData = $stmtP->fetch(PDO::FETCH_ASSOC);
        $periodo = $pData['periodo_escolar'] ?? '2025-2026';
    }

    // 1. Verificación de Cédula Física (Si el estudiante ya tiene cédula V- o E-)
    if ($cedula_est) {
        $stmt = $pdo->prepare("SELECT id_estudiante FROM estudiantes WHERE cedula_es = ? AND periodo_escolar = ?");
        $stmt->execute([$cedula_est, $periodo]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Este estudiante ya está registrado en el periodo ' . $periodo]);
            exit;
        }
        echo json_encode(['status' => 'success', 'exists' => false]);
        exit;
    }

    // 2. Lógica para Cédula Escolar (CE) y Control de Gemelos
    if ($cedula_rep && $fecha_nac) {
        
        // Contamos cuántos estudiantes con la misma fecha de nacimiento tiene este representante en este periodo
        $sql = "SELECT COUNT(*) as gemelos FROM estudiantes 
                WHERE id_representante = (SELECT id_representante FROM representantes WHERE cedula_rep = ? LIMIT 1) 
                AND fecha_nacimiento = ? 
                AND periodo_escolar = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cedula_rep, $fecha_nac, $periodo]);
        $resGemelos = $stmt->fetch(PDO::FETCH_ASSOC);

        $cantidad_previa = $resGemelos['gemelos'] ?? 0;
        
        // Si ya existe Gabriela (1), Isabela recibirá el prefijo 2
        $prefijo = $cantidad_previa + 1;

        echo json_encode([
            'status' => 'success', 
            'prefijo' => $prefijo,
            'periodo' => $periodo,
            'pacto_multiple' => ($prefijo > 1)
        ]);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => "Error técnico: " . $e->getMessage()]);
}