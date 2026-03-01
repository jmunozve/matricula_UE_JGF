<?php
require_once 'includes/db.php';
$pdo = Conexion::abrir();

// 1. OBTENER DATOS DESDE TU VISTA
$sql_global = "SELECT 
    COUNT(*) as inscritos,
    SUM(CASE WHEN sexo_es IN ('M', '1') THEN 1 ELSE 0 END) as masc,
    SUM(CASE WHEN sexo_es IN ('F', '2') THEN 1 ELSE 0 END) as fem
    FROM vista_estudiantes_completa";
$global = $pdo->query($sql_global)->fetch(PDO::FETCH_ASSOC);

// 2. OBTENER CAPACIDAD (Desde tabla física)
$capacidad = $pdo->query("SELECT SUM(capacidad_max) FROM secciones")->fetchColumn() ?: 0;

// 3. DATOS POR NIVEL (Agrupados por tu vista)
$niveles = $pdo->query("SELECT nombre_nivel, COUNT(*) as cant FROM vista_estudiantes_completa GROUP BY nombre_nivel")->fetchAll(PDO::FETCH_ASSOC);

$inscritos = $global['inscritos'] ?: 0;
$vacantes = max(0, $capacidad - $inscritos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Directivo Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light p-4">

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Monitor de Matrícula Gil Fortoul</h2>
            <hr>
        </div>
    </div>

    <div class="row g-3 mb-4 text-white text-center">
        <div class="col-md-4">
            <div class="card bg-primary border-0 shadow-sm p-3">
                <small>INSCRITOS</small>
                <h1 class="fw-bold"><?= $inscritos ?></h1>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success border-0 shadow-sm p-3">
                <small>CAPACIDAD TOTAL</small>
                <h1 class="fw-bold"><?= $capacidad ?></h1>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning border-0 shadow-sm p-3">
                <small>CUPOS DISPONIBLES</small>
                <h1 class="fw-bold"><?= $vacantes ?></h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold">Distribución General</h5>
                <div style="height: 300px;">
                    <canvas id="chartDona"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold">Matrícula por Niveles</h5>
                <ul class="list-group list-group-flush mt-3">
                    <?php foreach($niveles as $n): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-mortarboard-fill text-primary me-2"></i><?= $n['nombre_nivel'] ?></span>
                        <span class="badge bg-light text-dark border"><?= $n['cant'] ?> Est.</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-auto pt-3">
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Varones: <?= $global['masc'] ?> | Hembras: <?= $global['fem'] ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('chartDona'), {
        type: 'doughnut',
        data: {
            labels: ['Inscritos', 'Vacantes'],
            datasets: [{
                data: [<?= $inscritos ?>, <?= $vacantes ?>],
                backgroundColor: ['#0d6efd', '#e9ecef'],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: { 
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
</script>

</body>
</html>