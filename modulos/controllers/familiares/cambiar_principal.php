<?php
require_once "../../config/conexion.php"; // Ajusta según tu ruta de conexión

if (isset($_POST['id_est_fam']) && isset($_POST['id_estudiante'])) {
    $id_est_fam = $_POST['id_est_fam'];
    $id_est_id = $_POST['id_estudiante'];

    try {
        $pdo->beginTransaction();

        // 1. Ponemos a todos los familiares de ESTE estudiante como NO principales (0)
        $sql1 = "UPDATE estudiante_familiares SET es_principal = 0 WHERE id_estudiante = ?";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$id_est_id]);

        // 2. Marcamos al familiar seleccionado como principal (1)
        $sql2 = "UPDATE estudiante_familiares SET es_principal = 1 WHERE id_est_fam = ?";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$id_est_fam]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Representante legal actualizado']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}