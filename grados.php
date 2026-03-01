<?php
require_once __DIR__ . '/includes/db.php';

try {
    $pdo = Conexion::abrir();

    // Configuración según tus tablas
    $id_nivel_inicial = 1; 
    $id_plan_inicial = 3;
    $codigo_plan = '20000'; 

    $grados = [
        '1ER GRUPO MATERNAL',
        '2DO GRUPO MATERNAL',
        '3ER GRUPO MATERNAL',
        '1ER GRUPO PREESCOLAR',
        '2DO GRUPO PREESCOLAR',
        '3ER GRUPO PREESCOLAR'
    ];

    $pdo->beginTransaction();

    $sql = "INSERT INTO grados (id_nivel, id_plan, nombre_grado, codigo_plan) 
            VALUES (:nivel, :plan, :nombre, :codigo)";
    $stmt = $pdo->prepare($sql);

    echo "<h2>Insertando Grados de Inicial (Maternal/Preescolar)</h2><ul>";

    foreach ($grados as $nombre) {
        $stmt->execute([
            ':nivel'  => $id_nivel_inicial,
            ':plan'   => $id_plan_inicial,
            ':nombre' => $nombre,
            ':codigo' => $codigo_plan
        ]);
        echo "<li>✅ Registrado: <strong>$nombre</strong></li>";
    }

    $pdo->commit();
    echo "</ul><p><strong>Grados de Inicial creados. Procede a cargar las áreas.</strong></p>";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo "<div style='color:red;'>Error: " . $e->getMessage() . "</div>";
}