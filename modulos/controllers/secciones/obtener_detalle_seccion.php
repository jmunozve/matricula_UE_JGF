<?php
// F:\xampp\htdocs\matricula\modulos\controllers\secciones\obtener_detalle_seccion.php
require_once dirname(__DIR__, 3) . '/includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) exit("<div class='alert alert-danger m-2'>ID de sección no válido.</div>");

function calcularEdad($fecha)
{
    if (empty($fecha) || $fecha == '0000-00-00') return "S/D";
    $nacimiento = new DateTime($fecha);
    $hoy = new DateTime();
    $edad = $hoy->diff($nacimiento);
    return $edad->y;
}

try {
    $pdo = Conexion::abrir();

    // 1. Datos de la sección (Ajustada la jerarquía de tablas)
    $sql = "SELECT s.*, g.nombre_grado, n.nombre_nivel, g.id_grado
            FROM secciones s
            LEFT JOIN grados g ON s.id_grado = g.id_grado
            LEFT JOIN planes_estudio p ON g.id_plan = p.id_plan
            LEFT JOIN niveles_estudio n ON p.id_nivel = n.id_nivel 
            WHERE s.id_seccion = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Estudiantes + Representantes
    $sqlEst = "SELECT e.id_estudiante, e.cedula_es, e.cedula_escolar, e.nombre_es, e.apellido_es, 
                      e.sexo_es, e.fecha_nacimiento,
                      r.nombre_rep, r.apellido_rep, r.cedula_rep, r.tel_rep
               FROM estudiantes e
               LEFT JOIN representantes r ON e.id_representante = r.id_representante
               WHERE e.id_seccion = ? 
               ORDER BY e.apellido_es ASC";

    $stmtEst = $pdo->prepare($sqlEst);
    $stmtEst->execute([$id]);
    $estudiantes = $stmtEst->fetchAll(PDO::FETCH_ASSOC);

    // 3. Consulta de Áreas de Formación
    $sqlAreas = "SELECT nombre_area FROM areas_formacion WHERE id_grado = ? ORDER BY orden ASC";
    $stmtAreas = $pdo->prepare($sqlAreas);
    $stmtAreas->execute([$info['id_grado']]);
    $materias = $stmtAreas->fetchAll(PDO::FETCH_ASSOC);

    // Contadores de género
    $m = 0;
    $f = 0;
    foreach ($estudiantes as $est) {
        if ($est['sexo_es'] == 1) $m++;
        elseif ($est['sexo_es'] == 2) $f++;
    }
?>

    <style>
        .stat-box {
            border-radius: 12px;
            padding: 15px;
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        .stat-val {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-lab {
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }

        .table-inline {
            font-size: 0.85rem !important;
        }

        .table-inline thead th {
            background-color: #0f172a;
            color: white;
            padding: 10px;
            border: none;
            font-size: 0.75rem;
        }

        .badge-sexo {
            width: 25px;
            height: 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
        }

        .btn-edit-student {
            transition: all 0.2s;
        }

        .btn-edit-student:hover {
            transform: scale(1.1);
        }
    </style>

    <div class="container-fluid py-2">
        <div class="row g-3 mb-4 text-center">
            <div class="col-md-3">
                <div class="stat-box shadow-sm border-bottom border-dark border-4">
                    <span class="stat-lab">Sección</span>
                    <span class="stat-val d-block"><?= htmlspecialchars($info['nombre_grado']) ?> "<?= $info['letra'] ?>"</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box shadow-sm">
                    <span class="stat-lab">Estudiantes</span>
                    <span class="stat-val d-block"><?= count($estudiantes) ?> / <?= $info['capacidad_max'] ?></span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box shadow-sm border-bottom border-primary border-4">
                    <span class="stat-lab text-primary">Masculinos</span>
                    <span class="stat-val text-primary d-block"><?= $m ?></span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box shadow-sm border-bottom border-danger border-4">
                    <span class="stat-lab" style="color: #d63384;">Femeninos</span>
                    <span class="stat-val d-block" style="color: #d63384;"><?= $f ?></span>
                </div>
            </div>
            <div class="row mb-3">
                <div class="row mb-3">
                    <div class="col-12 text-end">
                        <a href="/matricula/modulos/views/representante/registro.php?id_grado=<?= $info['id_grado'] ?>&id_seccion=<?= $id ?>"
                            class="btn btn-success shadow-sm fw-bold">
                            <i class="bi bi-person-plus-fill me-2"></i> Registrar Estudiante Aquí
                        </a>
                    </div>
                </div>

            </div>

            <ul class="nav nav-tabs mb-0 border-bottom-0" id="tabSeccion" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-alumnos" type="button">
                        <i class="bi bi-person-lines-fill"></i> Nómina de Estudiantes
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab-areas" type="button">
                        <i class="bi bi-book"></i> Áreas de Formación
                    </button>
                </li>
            </ul>

            <div class="tab-content border rounded-bottom bg-white shadow-sm">
                <div class="tab-pane fade show active" id="tab-alumnos">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-inline">
                            <thead>
                                <tr class="text-uppercase">
                                    <th width="50">#</th>
                                    <th>Identificación</th>
                                    <th>Nombres y Apellidos</th>
                                    <th class="text-center">Edad</th>
                                    <th class="text-center">Sexo</th>
                                    <th>Representante</th>
                                    <th>Contacto</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                foreach ($estudiantes as $e):
                                    $es_f = ($e['sexo_es'] == 2);
                                    $cedula_v = !empty($e['cedula_es']) ? $e['cedula_es'] : $e['cedula_escolar'];
                                ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $i++ ?></td>
                                        <td class="text-secondary fw-bold"><?= $cedula_v ?></td>
                                        <td class="fw-bold text-uppercase"><?= htmlspecialchars($e['apellido_es'] . " " . $e['nombre_es']) ?></td>
                                        <td class="text-center"><?= calcularEdad($e['fecha_nacimiento']) ?> años</td>
                                        <td class="text-center">
                                            <span class="badge-sexo <?= $es_f ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' ?>">
                                                <?= $es_f ? 'F' : 'M' ?>
                                            </span>
                                        </td>
                                        <td class="text-uppercase small"><?= htmlspecialchars($e['nombre_rep'] . " " . $e['apellido_rep']) ?></td>
                                        <td>
                                            <i class="bi bi-telephone text-success me-1"></i><?= $e['tel_rep'] ?: '---' ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary rounded-pill px-3"
                                                onclick="abrirModalEditar(<?= $e['id_estudiante'] ?>, '/matricula/')"
                                                title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade p-4" id="tab-areas">
                    <div class="row g-3">
                        <?php if (empty($materias)): ?>
                            <div class="col-12 text-center py-5">
                                <p class="text-muted">No hay áreas configuradas para este grado.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($materias as $mat): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="p-3 border rounded shadow-sm bg-light">
                                        <h6 class="mb-0 fw-bold text-uppercase small"><?= htmlspecialchars($mat['nombre_area']) ?></h6>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
}
    ?>