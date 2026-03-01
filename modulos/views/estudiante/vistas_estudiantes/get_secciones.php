<?php
// vistas_estudiantes/get_estudiantes_seccion.php
require_once "../../../includes/db.php"; 

$id_seccion = $_GET['id_seccion'] ?? null;

if (!$id_seccion) {
    die("<div class='p-3 text-danger small'>ID de sección no recibido.</div>");
}

try {
    $pdo = Conexion::abrir();
    
    // Usamos tu vista_estudiantes_completa
    // Ya no necesitamos JOINs manuales porque la vista los trae
    $sql = "SELECT id_estudiante, cedula_escolar, nombre_es, apellido_es, sexo_es, estatus 
            FROM vista_estudiantes_completa 
            WHERE id_seccion = :id 
            ORDER BY apellido_es ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id_seccion]);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($estudiantes)): ?>
        <div class="p-4 text-center text-muted border-top">
            <i class="bi bi-person-exclamation fs-4 d-block mb-2"></i>
            <p class="small mb-0">No hay alumnos inscritos en esta sección aún.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" style="font-size: 0.85rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3 border-0">Cédula Escolar</th>
                        <th class="border-0">Nombre del Estudiante</th>
                        <th class="text-center border-0">Género</th>
                        <th class="text-center border-0">Estado</th>
                        <th class="text-end pe-3 border-0">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estudiantes as $est): ?>
                    <tr class="align-middle">
                        <td class="ps-3 fw-bold text-dark"><?= $est['cedula_escolar'] ?></td>
                        <td><?= htmlspecialchars($est['apellido_es'] . ", " . $est['nombre_es']) ?></td>
                        <td class="text-center">
                            <span class="badge rounded-pill <?= $est['sexo_es'] == 'M' ? 'bg-light text-primary' : 'bg-light text-danger' ?> border">
                                <?= $est['sexo_es'] ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge <?= $est['estatus'] == 'ACTIVO' ? 'text-success' : 'text-danger' ?> small">
                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> <?= $est['estatus'] ?>
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-secondary border-0" onclick="verFicha(<?= $est['id_estudiante'] ?>)" title="Ver Ficha">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary border-0" onclick="editarEstudiante(<?= $est['id_estudiante'] ?>)" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif;
} catch (Exception $e) {
    echo "<div class='p-3 text-danger small'>Error en la base de datos: " . $e->getMessage() . "</div>";
}