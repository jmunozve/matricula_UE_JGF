<?php
/**
 * F:\xampp\htdocs\matricula\modulos\views\secciones\crear_seccion.php
 */
if (session_status() === PHP_SESSION_NONE) session_start();
include_once "../../../includes/header.php";
require_once "../../../includes/db.php";

$id_grado_url = isset($_GET['id_grado']) ? (int)$_GET['id_grado'] : 0;

try {
    $pdo = Conexion::abrir();
    
    // Consulta directa para asegurar que el SELECT de grados se llene
    $stmtGrados = $pdo->prepare("SELECT id_grado, nombre_grado FROM grados WHERE id_plan = 4 ORDER BY id_grado ASC");
    $stmtGrados->execute();
    $grados = $stmtGrados->fetchAll(PDO::FETCH_ASSOC);

    $profesores = $pdo->query("SELECT id_docente, nombre, apellido FROM personal WHERE estatus = 'Activo' ORDER BY apellido ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error crítico: " . $e->getMessage());
}
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="bi bi-plus-square me-2"></i> Configurar Sección de Primaria</h5>
            <span class="badge bg-white text-primary px-3">Plan 21000 (ID: 4)</span>
        </div>
        <div class="card-body">
            <form action="../../controllers/secciones/guardar_seccion.php" method="POST">
                
                <input type="hidden" name="id_plan" value="4">
                <input type="hidden" name="id_nivel" value="2">
                
                <?php if ($id_grado_url > 0): ?>
                    <input type="hidden" name="id_grado" value="<?= $id_grado_url ?>">
                <?php endif; ?>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">GRADO</label>
                        <select class="form-select <?= ($id_grado_url > 0) ? 'bg-light fw-bold' : '' ?>" 
                                id="id_grado" 
                                name="<?= ($id_grado_url > 0) ? '' : 'id_grado' ?>" 
                                <?= ($id_grado_url > 0) ? 'disabled' : 'required' ?>
                                onchange="cargarAreasFormacion(this.value)">
                            
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($grados as $g): ?>
                                <option value="<?= $g['id_grado'] ?>" <?= ($id_grado_url == $g['id_grado']) ? 'selected' : '' ?>>
                                    <?= $g['nombre_grado'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">SECCIÓN</label>
                        <select class="form-select text-center fw-bold" name="letra_seccion" required>
                            <?php foreach (range('A', 'F') as $l): ?>
                                <option value="<?= $l ?>"><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">TURNO</label>
                        <select class="form-select" name="turno" required>
                            <option value="Mañana">Mañana</option>
                            <option value="Tarde">Tarde</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold">DOCENTE</label>
                        <select class="form-select" name="id_docente">
                            <option value="">Sin asignar...</option>
                            <?php foreach ($profesores as $p): ?>
                                <option value="<?= $p['id_docente'] ?>"><?= $p['apellido'] ?> <?= $p['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="panel-areas" class="mt-4 p-4 bg-light rounded-3 border" style="display:none;">
                    <h6 class="fw-bold text-muted border-bottom pb-2 mb-3">MATERIAS DEL GRADO</h6>
                    <div id="lista-areas" class="row row-cols-1 row-cols-md-3 g-2"></div>
                </div>

                <div class="text-end mt-4 pt-3 border-top">
                    <a href="lista.php" class="btn btn-outline-secondary px-4 me-2">
                        <i class="bi bi-x-circle me-1"></i> Cancelar y Volver
                    </a>
                    <button type="submit" class="btn btn-primary px-5 fw-bold shadow">
                        <i class="bi bi-check-lg me-1"></i> Guardar Sección
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const s = document.getElementById('id_grado');
    if (s.value) cargarAreasFormacion(s.value);
});

function cargarAreasFormacion(id) {
    const p = document.getElementById('panel-areas');
    const l = document.getElementById('lista-areas');
    if(!id) { p.style.display = 'none'; return; }

    fetch(`../../controllers/ubicacion/get_ubicacion.php?tipo=areas&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if(data.length > 0) {
                p.style.display = 'block';
                l.innerHTML = data.map(m => `
                    <div class="col">
                        <div class="p-2 border rounded bg-white small shadow-sm">
                            <i class="bi bi-check-circle text-success me-2"></i>${m.nombre}
                        </div>
                    </div>
                `).join('');
            }
        });
}
</script>