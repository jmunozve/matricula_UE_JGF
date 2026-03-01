<?php
require_once __DIR__ . '/includes/db.php';

try {
    $pdo = Conexion::abrir();
    $id_plan_inicial = 3; 

    // Áreas oficiales de Educación Inicial (Guía Pedagógica)
    $areas_inicial = [
        ['nombre' => 'FORMACIÓN PERSONAL Y SOCIAL', 'orden' => 1],
        ['nombre' => 'RELACIÓN CON EL AMBIENTE', 'orden' => 2],
        ['nombre' => 'COMUNICACIÓN Y REPRESENTACIÓN', 'orden' => 3]
    ];

    $pdo->beginTransaction();

    // Buscamos los grados de Inicial (Maternal y Preescolar)
    $stmtGrados = $pdo->prepare("SELECT id_grado, nombre_grado FROM grados WHERE id_plan = ?");
    $stmtGrados->execute([$id_plan_inicial]);
    $grados = $stmtGrados->fetchAll(PDO::FETCH_ASSOC);

    $sqlInsert = "INSERT INTO areas_formacion (id_plan, id_grado, nombre_area, orden) VALUES (:plan, :grado, :nombre, :orden)";
    $stmtInsert = $pdo->prepare($sqlInsert);

    echo "<h2>Asignando Áreas a Educación Inicial</h2><ul>";

    foreach ($grados as $g) {
        foreach ($areas_inicial as $area) {
            $stmtInsert->execute([
                ':plan'   => $id_plan_inicial,
                ':grado'  => $g['id_grado'],
                ':nombre' => $area['nombre'],
                ':orden'  => $area['orden']
            ]);
        }
        echo "<li>🎨 Áreas asignadas a: <strong>{$g['nombre_grado']}</strong></li>";
    }

    $pdo->commit();
    echo "</ul><p><strong>Configuración de Inicial completada.</strong></p>";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo "<div style='color:red;'>Error: " . $e->getMessage() . "</div>";
}