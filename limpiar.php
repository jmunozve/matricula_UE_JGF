<?php
/**
 * F:\xampp\htdocs\matricula\limpiar.php
 * Script para resetear los datos de prueba
 */

// Intentamos detectar la ruta automáticamente
$ruta_db = __DIR__ . '/includes/db.php';

if (!file_exists($ruta_db)) {
    die("Error: No se pudo encontrar el archivo de conexión en: " . $ruta_db);
}

require_once $ruta_db; 

try {
    $pdo = Conexion::abrir();

    // 1. Desactivar temporalmente las restricciones de llave foránea para poder usar TRUNCATE
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 2. Vaciar tablas y reiniciar IDs (TRUNCATE resetea el contador auto_increment a 1)
    
    // Primero los familiares (hijos de la relación)
    $pdo->exec("TRUNCATE TABLE estudiante_familiares");
    echo "✓ Tabla 'estudiante_familiares' vaciada.<br>";

    // Luego los estudiantes
    $pdo->exec("TRUNCATE TABLE estudiantes");
    echo "✓ Tabla 'estudiantes' vaciada.<br>";

    // Finalmente los representantes
    $pdo->exec("TRUNCATE TABLE representantes");
    echo "✓ Tabla 'representantes' vaciada.<br>";

    // 3. Reactivar las restricciones de llave foránea
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "<br><div style='color: green;'><strong>Base de datos reseteada con éxito. Todos los registros han sido eliminados.</strong></div>";

} catch (Exception $e) {
    echo "<div style='color: red;'>Error técnico: " . $e->getMessage() . "</div>";
}