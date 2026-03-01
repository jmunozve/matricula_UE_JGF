<?php
/**
 * validar_preinscritos.php - Proceso de Inscripción Directa
 */
session_start(); 
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';

$id_estudiante = isset($_GET['id']) ? (int)$_GET['id'] : null;
$id_seccion    = isset($_GET['id_seccion']) ? (int)$_GET['id_seccion'] : null; 

if ($id_estudiante && $id_seccion) {
    try {
        $pdo = Conexion::abrir();
        
        // Actualización simplificada: solo sección y estatus
        $sql = "UPDATE estudiantes SET 
                    id_seccion = :id_sec, 
                    estatus    = 'Inscrito' 
                WHERE id_estudiante = :id_est";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_sec' => $id_seccion, 
            ':id_est' => $id_estudiante
        ]);

        header("Location: ../../views/estudiante/validar_inscripciones.php?status=success");
        exit;

    } catch (Exception $e) {
        die("Error crítico en la base de datos: " . $e->getMessage());
    }
} else {
    header("Location: ../../views/estudiante/validar_inscripciones.php?status=error");
    exit;
}