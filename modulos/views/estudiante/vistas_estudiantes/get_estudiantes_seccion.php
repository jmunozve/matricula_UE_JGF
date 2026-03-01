<?php
require_once "../../../../includes/db.php";

$id_seccion = $_GET['id_seccion'] ?? null;
$nivel_filtro = $_GET['nivel_filtro'] ?? null;

if (!$id_seccion) die("Parámetros insuficientes.");

try {
    $pdo = Conexion::abrir();
    
    $sql = "SELECT e.id_estudiante, e.nombre, e.apellido, e.cedula, e.cedula_escolar, e.sexo
            FROM estudiante e
            JOIN secciones s ON e.id_seccion = s.id_seccion
            JOIN grados g ON s.id_grado = g.id_grado
            JOIN niveles_estudio n ON g.id_nivel = n.id_nivel
            WHERE e.id_seccion = :id_seccion";

    if (!empty($nivel_filtro)) {
        $sql .= " AND n.nombre_nivel = :nivel";
    }

    $sql .= " ORDER BY e.apellido ASC";

    $stmt = $pdo->prepare($sql);
    $params = [':id_seccion' => $id_seccion];
    if (!empty($nivel_filtro)) $params[':nivel'] = $nivel_filtro;
    
    $stmt->execute($params);
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<?php if (empty($alumnos)): ?>
    <div class="p-3 text-center text-muted small">
        <i class="bi bi-info-circle me-1"></i> No hay estudiantes registrados en este nivel.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle table-hover mb-0" style="font-size: 0.82rem;">
            <thead class="bg-light text-muted">
                <tr>
                    <th class="ps-3">Estudiante</th>
                    <th>Identificación</th>
                    <th class="text-center">Sexo</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alumnos as $al): 
                    $cedula_display = !empty($al['cedula']) ? $al['cedula'] : $al['cedula_escolar'];
                    
                    // LÓGICA ESTRICTA DE SEXO
                    // 2 = Femenino (Rosado), 1 = Masculino (Azul)
                    if ($al['sexo'] == 2) {
                        $letra = "F";
                        $sexo_icon = "bi-person-heart";
                        $badge_style = "background-color: #fceef5; color: #d63384; border: 1px solid #f5c5d9;";
                    } else {
                        $letra = "M";
                        $sexo_icon = "bi-person-fill";
                        $badge_style = "background-color: #e7f1ff; color: #0d6efd; border: 1px solid #b6d4fe;";
                    }
                ?>
                    <tr>
                        <td class="ps-3 fw-bold text-dark">
                            <?= htmlspecialchars($al['apellido'] . " " . $al['nombre']) ?>
                        </td>
                        <td>
                            <span class="text-muted small">C.I:</span> <?= htmlspecialchars($cedula_display) ?>
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill px-2 py-1" style="<?= $badge_style ?> font-size: 0.75rem; min-width: 45px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="bi <?= $sexo_icon ?> me-1"></i> <?= $letra ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-link text-info p-1" title="Ver Detalle" 
                                        onclick="verEstudiante(<?= $al['id_estudiante'] ?>)">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                                <button class="btn btn-link text-warning p-1" title="Editar" 
                                        onclick="editarEstudiante(<?= $al['id_estudiante'] ?>)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>