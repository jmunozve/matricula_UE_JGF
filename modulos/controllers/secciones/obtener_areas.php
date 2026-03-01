<?php
require_once "../../../includes/db.php";
$id_plan = isset($_GET['id_plan']) ? intval($_GET['id_plan']) : 0;

if ($id_plan > 0) {
    $pdo = Conexion::abrir();
    // Ajustado a tu requerimiento: nombre de tabla/campo "area_formacion"
    $sql = "SELECT nombre_area FROM areas_formacion WHERE id_plan = ? ORDER BY orden ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_plan]);
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($areas) > 0) {
        foreach ($areas as $a) {
            echo "<tr><td class='ps-3 py-2 text-uppercase fw-semibold' style='font-size: 0.8rem; color: #444;'>";
            echo htmlspecialchars($a['nombre_area']);
            echo "</td></tr>";
        }
    } else {
        echo "<tr><td class='text-center py-4 text-muted small italic'>Este plan no tiene áreas de formación registradas.</td></tr>";
    }
}