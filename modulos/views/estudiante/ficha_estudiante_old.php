<?php

/**
 * Ubicación: F:\xampp\htdocs\matricula\modulos\views\estudiante\ficha_estudiante.php
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../../../includes/db.php";

$id_estudiante = $_GET['id'] ?? null;
if (!$id_estudiante) die("ID de estudiante no proporcionado.");

try {
    $pdo = Conexion::abrir();

    // SQL Corregido: nombres de columnas exactos y JOIN a través de planes_estudio
    $sql = "SELECT e.*, 
                   r.nombre_rep, r.apellido_rep, r.cedula_rep, r.tel_rep as r_contacto,
                   s.letra as seccion_letra, s.turno as seccion_turno,
                   g.nombre_grado,
                   n.nombre_nivel,
                   pa.nombre_pais,
                   es.nombre_estado,
                   mu.nombre_municipio,
                   pr.nombre_parroquia
            FROM estudiantes e 
            LEFT JOIN representantes r ON e.id_representante = r.id_representante 
            LEFT JOIN secciones s ON e.id_seccion = s.id_seccion
            LEFT JOIN grados g ON s.id_grado = g.id_grado
            LEFT JOIN planes_estudio p ON g.id_plan = p.id_plan
            LEFT JOIN niveles_estudio n ON p.id_nivel = n.id_nivel
            LEFT JOIN paises pa ON e.id_pais_es = pa.id_pais
            LEFT JOIN estados es ON e.id_estado_hab = es.id_estado
            LEFT JOIN municipios mu ON e.id_mun_hab = mu.id_municipio
            LEFT JOIN parroquias pr ON e.id_parroquia_hab = pr.id_parroquia
            WHERE e.id_estudiante = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_estudiante]);
    $est = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$est) die("Estudiante no encontrado.");
} catch (Exception $e) {
    die("Error de Base de Datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Expediente - <?= htmlspecialchars($est['nombre_es']) ?></title>
    <link href="../../../public/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../public/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-size: 0.85rem;
            color: #334155;
        }

        .ficha-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 20px;
            border-bottom: 5px solid #1d4ed8;
        }

        .card-ficha {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            background: #fff;
        }

        .section-title {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1e3a8a;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 15px;
            padding-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 8px;
            font-size: 1rem;
        }

        .data-label {
            font-weight: 700;
            color: #64748b;
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
        }

        .data-value {
            display: block;
            padding: 4px 0 8px 0;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            font-weight: 500;
        }

        .img-perfil {
            width: 160px;
            height: 180px;
            object-fit: cover;
            border-radius: 12px;
            border: 4px solid #fff;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            background: #eee;
        }

        .rep-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .badge-status {
            font-size: 0.7rem;
            padding: 5px 12px;
            border-radius: 20px;
        }

        .sticky-footer {
            position: sticky;
            bottom: 0;
            background: #fff;
            padding: 15px;
            border-top: 1px solid #e2e8f0;
            text-align: right;
            z-index: 100;
        }

        .bg-academic {
            background: #f1f5f9;
            border-left: 4px solid #1e3a8a;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .sticky-footer {
                display: none;
            }

            body {
                background: #fff;
            }
        }
    </style>
</head>

<body>

    <div class="ficha-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1 fw-bold text-uppercase"><i class="bi bi-file-earmark-person me-2"></i>Expediente del Estudiante</h4>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-white text-primary fw-bold">ID: #<?= $est['id_estudiante'] ?></span>
                <span class="badge bg-info badge-status shadow-sm text-dark">ESTATUS: <?= strtoupper($est['estatus']) ?></span>
            </div>
        </div>
        <div class="no-print">
            <button class="btn btn-light btn-sm fw-bold shadow-sm" onclick="window.close()"><i class="bi bi-x-lg"></i></button>
        </div>
    </div>

    <div class="container-fluid p-4">
        <div class="row g-4">

            <div class="col-md-3">
                <div class="text-center mb-4">
                    <img src="../../../uploads/fotos_est/<?= !empty($est['foto_carnet']) ? $est['foto_carnet'] : 'default.png' ?>"
                        class="img-perfil"
                        onerror="this.src='../../../public/assets/img/default-user.png'">
                    <h5 class="mt-3 fw-bold text-primary mb-0"><?= htmlspecialchars($est['apellido_es'] . ", " . $est['nombre_es']) ?></h5>
                    <p class="text-muted small">C.I.: <?= $est['cedula_es'] ?></p>
                </div>

                <div class="card-ficha p-3 bg-academic">
                    <div class="section-title">
                        <i class="bi bi-book"></i> Académico
                    </div>
                    <div class="mb-2">
                        <span class="data-label">Nivel / Plan</span>
                        <span class="fw-bold"><?= $est['nombre_nivel'] ?></span>
                    </div>
                    <div class="mb-2">
                        <span class="data-label">Grado y Sección</span>
                        <span class="fw-bold"><?= $est['nombre_grado'] ?> - "<?= $est['seccion_letra'] ?>"</span>
                        <div class="small text-muted text-uppercase"><?= $est['seccion_turno'] ?></div>
                    </div>
                    <div class="mt-3">
                        <span class="badge w-100 <?= $est['es_repitiente'] ? 'bg-danger' : 'bg-success' ?>">
                            <?= $est['es_repitiente'] ? 'ALUMNO REPITIENTE' : 'ALUMNO REGULAR' ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-9">

                <div class="card-ficha p-4">
                    <div class="section-title"><i class="bi bi-person-badge"></i> Identificación y Nacimiento</div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <span class="data-label">Cédula Identidad</span>
                            <span class="data-value"><?= $est['tipo_doc_es'] ?>-<?= $est['cedula_es'] ?></span>
                        </div>
                        <div class="col-md-4">
                            <span class="data-label">Cédula Escolar (Control Gemelar)</span>
                            <span class="data-value fw-bold text-primary"><?= $est['cedula_escolar'] ?: 'No asignada' ?></span>
                        </div>
                        <div class="col-md-2">
                            <span class="data-label">Género</span>
                            <span class="data-value"><?= $est['sexo_es'] == 1 ? 'Masculino' : 'Femenino' ?></span>
                        </div>
                        <div class="col-md-3">
                            <span class="data-label">F. Nacimiento</span>
                            <span class="data-value"><?= $est['fecha_nacimiento'] ? date('d/m/Y', strtotime($est['fecha_nacimiento'])) : 'S/D' ?></span>
                        </div>
                    </div>
                </div>

                <div class="card-ficha p-4 border-start border-danger border-4">
                    <div class="section-title text-danger" style="border-color: #fee2e2;"><i class="bi bi-heart-pulse"></i> Información de Salud</div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <span class="data-label">Grupo Sanguíneo</span>
                            <span class="data-value fw-bold"><?= $est['tipo_sangre'] ?: 'S/D' ?></span>
                        </div>
                        <div class="col-md-3">
                            <span class="data-label">Discapacidad</span>
                            <span class="badge <?= $est['tiene_discapacidad'] == 'Si' ? 'bg-warning text-dark' : 'bg-light text-muted' ?>">
                                <?= $est['tiene_discapacidad'] ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span class="data-label">Detalles de Salud / Discapacidad</span>
                            <span class="data-value"><?= $est['detalle_discapacidad'] ?: 'Ninguna' ?></span>
                        </div>
                    </div>
                </div>

                <div class="card-ficha p-4">
                    <div class="section-title"><i class="bi bi-geo-alt"></i> Ubicación y Habitación</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <span class="data-label">Estado</span>
                            <span class="data-value"><?= $est['nombre_estado'] ?: 'No registrado' ?></span>
                        </div>
                        <div class="col-md-4">
                            <span class="data-label">Municipio</span>
                            <span class="data-value"><?= $est['nombre_municipio'] ?: 'No registrado' ?></span>
                        </div>
                        <div class="col-md-4">
                            <span class="data-label">Parroquia</span>
                            <span class="data-value"><?= $est['nombre_parroquia'] ?: 'No registrado' ?></span>
                        </div>
                        <div class="col-md-12">
                            <span class="data-label">Dirección de Habitación</span>
                            <span class="data-value"><?= htmlspecialchars($est['direccion_detalle']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="card-ficha p-4">
                    <div class="section-title"><i class="bi bi-people"></i> Representante Legal</div>
                    <div class="rep-card p-3 d-flex align-items-center gap-4">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                            <i class="bi bi-shield-check fs-3"></i>
                        </div>
                        <div class="row flex-grow-1">
                            <div class="col-md-5">
                                <span class="data-label">Nombre y Apellido</span>
                                <span class="fw-bold text-uppercase"><?= $est['nombre_rep'] ?> <?= $est['apellido_rep'] ?></span>
                            </div>
                            <div class="col-md-3">
                                <span class="data-label">Cédula Rep.</span>
                                <span><?= $est['cedula_rep'] ?></span>
                            </div>
                            <div class="col-md-4">
                                <span class="data-label">Teléfono / Contacto</span>
                                <span class="text-primary fw-bold"><i class="bi bi-whatsapp me-1"></i><?= $est['r_contacto'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="sticky-footer no-print">
        <span class="text-muted small me-4">Registrado por: <b><?= $est['registrado_por'] ?></b> el <?= date('d/m/Y', strtotime($est['fecha_registro'])) ?></span>
        <button class="btn btn-outline-primary btn-sm px-4 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Imprimir
        </button>
        <button class="btn btn-secondary btn-sm px-4" onclick="window.close()">Cerrar</button>
    </div>

</body>

</html>