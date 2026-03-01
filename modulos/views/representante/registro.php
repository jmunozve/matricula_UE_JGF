<?php

/**
 * registro.php - ESTRUCTURA FINAL CORREGIDA PARA V2
 */
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['ruta_activa'] = 'estudiante/registro';

require_once "../../../includes/db.php";
include_once "../../../includes/header.php";

$modalidades = $paises = $estados = $sexos = $parentescos = $nacionalidades = $planteles = $grados_primaria = [];

try {
    $pdo = Conexion::abrir();

    // Consultas directas simples
    $modalidades = $pdo->query("SELECT id_modalidad, nombre_modalidad FROM modalidades")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $paises      = $pdo->query("SELECT id_pais, nombre_pais FROM paises ORDER BY nombre_pais ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $estados     = $pdo->query("SELECT id_estado, nombre_estado FROM estados ORDER BY nombre_estado ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $sexos       = $pdo->query("SELECT id_sexo, nombre_sexo FROM sexos")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $tipos_sangre = $pdo->query("SELECT id_sangre, nombre_sangre FROM tipos_sangre ORDER BY id_sangre ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $parentescos = $pdo->query("SELECT id_parentesco, nombre_parentesco, es_representante_legal FROM parentescos")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $nacionalidades = $pdo->query("SELECT id_nacionalidad, nombre_nacionalidad FROM nacionalidades")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $planteles   = $pdo->query("SELECT id_plantel, nombre_plantel, codigo_dea FROM planteles ORDER BY nombre_plantel ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $stmtPeriodo = $pdo->query("SELECT periodo_escolar FROM planteles LIMIT 1");
    $plantel_dato = $stmtPeriodo->fetch(PDO::FETCH_ASSOC);
    $periodo_escolar = $plantel_dato['periodo_escolar'] ?? '2025-2026';


    // CORRECCIÓN 1: Un solo signo de $ y cierre correcto
    $stmtGrados = $pdo->prepare("SELECT id_grado, nombre_grado FROM grados WHERE id_plan = 4 ORDER BY id_grado ASC");
    $stmtGrados->execute();
    $grados_primaria = $stmtGrados->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // CORRECCIÓN 2: Consulta de discapacidades cerrada correctamente
    $discapacidades_data = $pdo->query("
        SELECT 
            CASE 
                WHEN c.nombre = 'Neurodivergente' THEN 'Neurodiversidad / Condición del Neurodesarrollo'
                WHEN c.nombre = 'Orgánica' THEN 'Discapacidad Orgánica o Motora'
                ELSE 'Otras Condiciones Especiales'
            END AS categoria,
            s.id AS subtipo_id,
            s.nombre AS subtipo_nombre,
            s.descripcion_especifica 
        FROM categorias_discapacidad c
        INNER JOIN subtipos_discapacidad s ON c.id = s.categoria_id
        ORDER BY categoria, s.nombre
    ")->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("Error en registro.php: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Error técnico: " . $e->getMessage() . "</div>";
}
?>

<style>
    /* Reduce el espacio entre todas las columnas del formulario */
    .row {
        --bs-gutter-y: 0.5rem;
        /* Reduce el espacio vertical entre filas */
    }

    /* Hace que las cards ocupen menos espacio visual */
    .card {
        margin-bottom: 0.75rem !important;
    }

    .card-body {
        padding: 0.8rem !important;
        /* Estrecha el contenido interno */
    }

    /* Reduce la distancia entre el label y el input */
    .form-label {
        margin-bottom: 2px;
        font-size: 0.85rem;
    }

    body {
        background-color: #f0f2f5;
    }

    .main-container {
        padding: 40px 10%;
    }

    .card-wizard {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        background: #fff;
    }

    .nav-pills-custom .nav-link {
        color: #495057;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        margin: 0 10px;
        padding: 15px 25px;
        transition: all 0.3s;
    }

    .nav-pills-custom .nav-link.active {
        color: #fff;
        background: #4e73df;
        border-color: #4e73df;
    }

    .section-title-blue {
        font-size: 0.85rem;
        font-weight: 700;
        color: #4e73df;
        text-transform: uppercase;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .section-title-blue i {
        margin-right: 8px;
        font-size: 1.1rem;
    }

    .upload-box {
        border: 1px dashed #d1d3e2;
        border-radius: 15px;
        padding: 25px;
        background: #f8f9fc;
        transition: 0.3s;
        cursor: pointer;
        text-align: center;
        margin-bottom: 15px;
    }

    .upload-box:hover {
        border-color: #4e73df;
        background: #fff;
    }

    .upload-box p {
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 10px;
        color: #5a5c69;
    }

    .form-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #3a3b45;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        padding: 10px;
        font-size: 0.9rem;
    }

    /* Clase para etiquetas obligatorias */
    .required-label::after {
        content: " *";
        color: red;
        font-weight: bold;
    }

    /* Opcional: resaltar el borde del input vacío */
    .is-invalid-custom {
        border: 2px solid #dc3545 !important;
    }
</style>

<div class="container-fluid main-container">
    <form id="formRegistroCompleto" method="POST" enctype="multipart/form-data" onsubmit="return false;">>
        <input type="hidden" name="periodo_escolar" id="periodo_escolar" value="<?php echo $periodo_escolar; ?>">
        <div class="d-flex justify-content-center mb-5">
            <ul class="nav nav-pills nav-pills-custom" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active nav-link-rep" id="tab-rep" data-bs-toggle="pill" data-bs-target="#step1" type="button">
                        1. REPRESENTANTE
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-est" data-bs-toggle="pill" data-bs-target="#step2" type="button">
                        2. ESTUDIANTE
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="step1" role="tabpanel">
                <div class="card card-wizard p-5 shadow-sm">
                    <div class="row">
                        <div class="col-lg-3 text-center border-end pe-lg-4">
                            <h6 class="text-uppercase small fw-bold text-muted mb-4">DOCUMENTACIÓN</h6>

                            <div class="upload-box border rounded mb-4" style="height: 180px; width: 100%; max-width: 180px; margin: 0 auto; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; cursor: pointer;" onclick="document.getElementById('foto_carnet_rep').click()">
                                <img id="pre_foto_rep" src="#" style="display: none; width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 10;">
                                <div id="placeholder_foto_rep" style="position: relative; z-index: 1;">
                                    <i class="bi bi-person-bounding-box fs-1 text-muted"></i>
                                    <p class="mb-0 small text-muted">Foto Representante</p>
                                </div>
                                <input type="file" name="foto_carnet_rep" id="foto_carnet_rep" class="d-none" accept="image/*" onchange="visualizarArchivo(this, 'pre_foto_rep', false)">
                            </div>

                            <div class="upload-box border rounded p-2" id="contenedor-pdf-rep" style="cursor: pointer;" onclick="document.getElementById('pdf_cedula_rep').click()">
                                <i class="bi bi-file-earmark-pdf text-danger fs-1"></i>
                                <p class="mb-0 small" id="pdf-name-rep">Cédula PDF</p>
                                <input type="file" name="pdf_cedula_rep" id="pdf_cedula_rep" class="d-none" accept="application/pdf" onchange="visualizarArchivo(this, 'contenedor-pdf-rep', true)">
                            </div>
                        </div>

                        <div class="col-lg-9 ps-lg-5">
                            <h5 class="section-title-blue text-uppercase">Datos del Representante</h5>
                            <hr class="mt-1 mb-4">

                            <div class="card border-success mb-4 shadow-sm">
                                <div class="card-header bg-success text-white py-2">
                                    <h6 class="mb-0 small"><i class="bi bi-person-badge-fill me-2"></i>DATOS PERSONALES</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-danger text-uppercase">* Cédula de Identidad</label>
                                            <div class="input-group input-group-sm">
                                                <select name="tipo_doc_rep" id="tipo_doc_rep" class="input-group-text bg-light fw-bold">
                                                    <option value="V">V</option>
                                                    <option value="E">E</option>
                                                    <option value="P">P</option>
                                                </select>
                                                <input type="text" name="cedula_rep" id="cedula_rep" class="form-control" placeholder="12345678" required>
                                                <button class="btn btn-success" type="button" id="btnBuscarRep"><i class="bi bi-search"></i></button>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-uppercase">Parentesco Legal</label>
                                            <select name="parentesco_rep" id="parentesco_rep" class="form-select form-select-sm" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($parentescos as $p): ?>
                                                    <?php if ($p['es_representante_legal'] == 1): ?>
                                                        <option value="<?= $p['id_parentesco'] ?>"><?= $p['nombre_parentesco'] ?></option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-uppercase">Sexo</label>
                                            <select name="sexo_rep" id="sexo_rep" class="form-select form-select-sm" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($sexos as $s): ?>
                                                    <option value="<?= $s['id_sexo'] ?>"><?= $s['nombre_sexo'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-uppercase">Nombres</label>
                                            <input type="text" name="nombre_rep" id="nombre_rep" class="form-control form-control-sm" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-uppercase">Apellidos</label>
                                            <input type="text" name="apellido_rep" id="apellido_rep" class="form-control form-control-sm" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-uppercase">Fecha de Nacimiento</label>
                                            <input type="date" name="fecha_nac_rep" id="fecha_nac_rep" class="form-control form-control-sm">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-uppercase">País de Origen</label>
                                            <select name="id_pais_rep" id="id_pais_rep" class="form-select form-select-sm">
                                                <?php foreach ($paises as $p): ?>
                                                    <option value="<?= $p['id_pais'] ?>" <?= ($p['id_pais'] == 232) ? 'selected' : '' ?>><?= $p['nombre_pais'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-uppercase">Nacionalidad</label>
                                            <select name="id_nacionalidad_rep" id="id_nacionalidad_rep" class="form-select form-select-sm">
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($nacionalidades as $n): ?>
                                                    <option value="<?= $n['id_nacionalidad'] ?>"><?= $n['nombre_nacionalidad'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-success mb-4 shadow-sm">
                                <div class="card-header bg-success text-white py-2">
                                    <h6 class="mb-0 small"><i class="bi bi-geo-alt-fill me-2"></i>UBICACIÓN Y CONTACTO</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-uppercase">Estado</label>
                                            <select name="id_estado_rep" id="id_estado_rep" class="form-select form-select-sm" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($estados as $e): ?>
                                                    <option value="<?= $e['id_estado'] ?>"><?= $e['nombre_estado'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-uppercase">Municipio</label>
                                            <select name="id_municipio_rep" id="id_municipio_rep" class="form-select form-select-sm" disabled required>
                                                <option value="">Seleccione...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-uppercase">Parroquia</label>
                                            <select name="id_parroquia_rep" id="id_parroquia_rep" class="form-select form-select-sm" disabled required>
                                                <option value="">Seleccione...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label small fw-bold text-uppercase">Dirección Exacta</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light"><i class="bi bi-map"></i></span>
                                                <input name="direccion_detalle_rep" id="direccion_detalle_rep" class="form-control" placeholder="Av, Calle, Casa, Referencia..." required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-uppercase">Teléfono</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                                <input type="text" name="tel_rep" id="tel_rep" class="form-control" placeholder="04XX-0000000" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-uppercase">Correo Electrónico</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light"><i class="bi bi-envelope-at"></i></span>
                                                <input type="email" name="correo_rep" id="correo_rep" class="form-control" placeholder="ejemplo@correo.com">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm" id="btnSiguienteRep">
                                    Siguiente Paso <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="step2" role="tabpanel">
                <div class="card card-wizard p-5 shadow-sm">
                    <div class="row">
                        <div class="col-lg-3 text-center border-0 pe-lg-4">
                            <h6 class="text-uppercase small fw-bold text-muted mb-4">FOTO CARNET</h6>
                            <div class="upload-box border rounded mb-4" id="contenedor_foto_est" style="height: 180px; width: 100%; max-width: 180px; margin: 0 auto; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; cursor: pointer;" onclick="document.getElementById('foto_carnet').click()">
                                <img id="pre_foto_est" src="#" style="display: none; width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 10;">
                                <button type="button" id="btn_remover_foto" class="btn btn-danger btn-sm" style="display: none; position: absolute; top: 5px; right: 5px; z-index: 20;" onclick="event.stopPropagation(); removerFotoEst();">
                                    <i class="bi bi-x"></i>
                                </button>
                                <div id="placeholder_foto_est" style="position: relative; z-index: 1;" class="text-center">
                                    <i class="bi bi-person-badge fs-1 text-muted"></i>
                                    <p class="mb-0 small text-muted">Foto Estudiante</p>
                                </div>
                                <input type="file" name="foto_carnet" id="foto_carnet" class="d-none" accept="image/*" onchange="visualizarArchivo(this, 'pre_foto_est', false)">
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6 d-flex flex-column gap-2">
                                    <div class="upload-box p-2 border rounded" id="cont-pdf_partida" style="cursor:pointer;" onclick="document.getElementById('doc_partida').click()">
                                        <i class="bi bi-file-earmark-medical text-primary fs-2"></i>
                                        <p class="mb-0 small" id="text-pdf_partida">Partida Nac.</p>
                                        <input type="file" name="doc_partida" id="doc_partida" class="d-none" accept="application/pdf" onchange="visualizarArchivo(this, 'cont-pdf_partida', true)">
                                    </div>
                                    <div id="div_pdf_cedula">
                                        <div class="upload-box p-2 border rounded" id="cont-pdf_cedula" style="cursor:pointer;" onclick="document.getElementById('doc_cedula').click()">
                                            <i class="bi bi-file-earmark-person text-danger fs-2"></i>
                                            <p class="mb-0 small" id="text-pdf_cedula">Cédula Alumno</p>
                                            <input type="file" name="doc_cedula" id="doc_cedula" class="d-none" accept="application/pdf" onchange="visualizarArchivo(this, 'cont-pdf_cedula', true)">
                                        </div>
                                    </div>
                                    <div class="upload-box p-2 border rounded" id="cont-pdf_boleta" style="cursor:pointer;" onclick="document.getElementById('doc_boleta').click()">
                                        <i class="bi bi-file-earmark-check text-success fs-2"></i>
                                        <p class="mb-0 small" id="text-pdf_boleta">Boleta Promoción</p>
                                        <input type="file" name="doc_boleta" id="doc_boleta" class="d-none" accept="application/pdf" onchange="visualizarArchivo(this, 'cont-pdf_boleta', true)">
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex flex-column gap-2">
                                    <div class="upload-box p-2 border rounded" id="cont-pdf_sano" style="cursor:pointer;" onclick="document.getElementById('doc_sano').click()">
                                        <i class="bi bi-heart-pulse text-info fs-2"></i>
                                        <p class="mb-0 small" id="text-pdf_sano">Const. Niño Sano</p>
                                        <input type="file" name="doc_sano" id="doc_sano" class="d-none" accept="application/pdf" onchange="visualizarArchivo(this, 'cont-pdf_sano', true)">
                                    </div>
                                    <div class="upload-box p-2 border rounded" id="cont-pdf_vacunas" style="cursor:pointer;" onclick="document.getElementById('doc_vacunas').click()">
                                        <i class="bi bi-capsule text-warning fs-2"></i>
                                        <p class="mb-0 small" id="text-pdf_vacunas">Cartón Vacunas</p>
                                        <input type="file" name="doc_vacunas" id="doc_vacunas" class="d-none" accept="application/pdf" onchange="visualizarArchivo(this, 'cont-pdf_vacunas', true)">
                                    </div>
                                    <!--       <div class="upload-box p-2 border rounded" id="cont-pdf_otros" style="cursor:pointer;" onclick="document.getElementById('doc_otros').click()">
                                        <i class="bi bi-file-earmark-plus text-secondary fs-2"></i>
                                        <p class="mb-0 small" id="text-pdf_otros">Otros Documentos</p>
                                        <input type="file" name="doc_otros" id="doc_otros" class="d-none" accept="application/pdf" onchange="visualizarArchivo(this, 'cont-pdf_otros', true)">
                                    </div>-->
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-9 ps-lg-5">
                            <h5 class="section-title-blue"><i class="bi bi-person-circle"></i> DATOS DEL ESTUDIANTE</h5>
                            <div class="card border-primary mb-4 shadow-sm">
                                <div class="card-header bg-primary text-white py-2">
                                    <h6 class="mb-0 small">
                                        <i class="bi bi-person-badge-fill me-2"></i>Identificación y Datos del Estudiante
                                    </h6>
                                </div>

                                <div class="card-body">
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-uppercase">¿Posee documento de identidad?</label>
                                            <div class="d-flex gap-3 mt-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tiene_ci" id="ci_si" value="SI" checked onchange="gestionarIdentidad()">
                                                    <label class="form-check-label fw-bold" for="ci_si">SÍ</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tiene_ci" id="ci_no" value="NO" onchange="gestionarIdentidad()">
                                                    <label class="form-check-label fw-bold" for="ci_no">NO (Escolar)</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6" id="contenedor_cedula">
                                            <label id="label_id_est" class="form-label fw-bold small text-uppercase">Número de Identificación</label>
                                            <div class="input-group input-group-sm">
                                                <select class="form-select flex-grow-0" style="width: 70px;" id="tipo_doc_es" name="tipo_doc_es">
                                                    <option value="V">V</option>
                                                    <option value="E">E</option>
                                                    <option value="P">P</option>
                                                    <option value="CD">CD</option>
                                                </select>
                                                <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                                                <input type="text" id="cedula_es" name="cedula_es" class="form-control" placeholder="Ingrese identificación">
                                            </div>
                                            <div id="msg_validacion" class="form-text mt-2 text-primary small"></div>
                                        </div>
                                    </div>

                                    <hr class="text-primary opacity-25 my-4">

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <p class="text-primary fw-bold small mb-2"><i class="bi bi-person-bounding-box me-1"></i> INFORMACIÓN BÁSICA</p>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-uppercase">Nombres</label>
                                            <input type="text" name="nombre_es" id="nombre_es" class="form-control form-control-sm" placeholder="Nombres del estudiante" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-uppercase">Apellidos</label>
                                            <input type="text" name="apellido_es" id="apellido_es" class="form-control form-control-sm" placeholder="Apellidos del estudiante" required>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small text-uppercase">Sexo</label>
                                            <select name="sexo_es" id="sexo_es" class="form-select form-select-sm" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($sexos as $s): ?>
                                                    <option value="<?= $s['id_sexo'] ?>"><?= $s['nombre_sexo'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small text-uppercase">Fecha Nac.</label>
                                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control form-control-sm" required onchange="procesarLogicaCedula()">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small text-uppercase">País Nacimiento</label>
                                            <select name="id_pais_nac" id="id_pais_nac" class="form-select form-select-sm" onchange="verificarPaisNacimiento(this.value)" required>
                                                <?php foreach ($paises as $p): ?>
                                                    <option value="<?= $p['id_pais'] ?>" <?= ($p['id_pais'] == 232) ? 'selected' : '' ?>>
                                                        <?= $p['nombre_pais'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small text-uppercase">Nacionalidad</label>
                                            <select name="nacionalidad_es" id="nacionalidad_es" class="form-select form-select-sm" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($nacionalidades as $n): ?>
                                                    <option value="<?= $n['id_nacionalidad'] ?>" <?= ($n['nombre_nacionalidad'] == 'VENEZOLANA') ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($n['nombre_nacionalidad']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-12 mt-4">
                                            <p class="text-primary fw-bold small mb-2"><i class="bi bi-geo-alt-fill me-1"></i> LUGAR DE NACIMIENTO (UBICACIÓN)</p>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small text-uppercase">Estado</label>
                                            <select name="id_estado_nac" id="id_estado_nac" class="form-select form-select-sm" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($estados as $e): ?>
                                                    <option value="<?= $e['id_estado'] ?>"><?= $e['nombre_estado'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small text-uppercase">Municipio</label>
                                            <select name="id_mun_nac" id="id_mun_nac" class="form-select form-select-sm" disabled required>
                                                <option value="">Esperando estado...</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small text-uppercase">Parroquia</label>
                                            <select name="id_parroquia_nac" id="id_parroquia_nac" class="form-select form-select-sm" disabled required>
                                                <option value="">Esperando municipio...</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3 mb-2">

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="copiar_direccion_check" onchange="copiarDireccionRepresentante()">
                                    <label class="form-check-label fw-bold text-primary" for="copiar_direccion_check">
                                        <i class="bi bi-geo-alt-fill me-1"></i> ¿Misma dirección del Representante?
                                    </label>
                                </div>
                            </div>

                            <div class="card border-primary mb-4 shadow-sm">
                                <div class="card-header bg-primary text-white py-2">
                                    <h6 class="mb-0 small">
                                        <i class="bi bi-house-door-fill me-2"></i>Dirección de Habitación
                                    </h6>
                                </div>

                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">ESTADO HAB.</label>
                                            <select name="id_estado_hab" id="id_estado_hab" class="form-select form-select-sm" onchange="cargarMunicipiosHab(this.value)">
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($estados as $e): ?>
                                                    <option value="<?= $e['id_estado'] ?>"><?= $e['nombre_estado'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">MUN. HAB.</label>
                                            <select name="id_mun_hab" id="id_mun_hab" class="form-select form-select-sm" disabled>
                                                <option value="">Seleccione municipio...</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">PARR. HAB.</label>
                                            <select name="id_parroquia_hab" id="id_parroquia_hab" class="form-select form-select-sm" disabled>
                                                <option value="">Seleccione parroquia...</option>
                                            </select>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-bold small">DIRECCIÓN DETALLADA (Calle, Vereda, Casa/Apto, Punto de Referencia)</label>
                                            <textarea id="direccion_hab_est" name="direccion_detalle" class="form-control form-control-sm" rows="2" placeholder="Ej: Calle 3, Vereda 5, Casa Nro 12, diagonal a la bodega..."></textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="card border-primary mb-4 shadow-sm">
                                <div class="card-header bg-primary text-white py-2">
                                    <h6 class="mb-0 small">
                                        <i class="bi bi-book me-2"></i>Información Académica y de Contacto
                                    </h6>
                                </div>

                                <div class="card-body">
                                    <input type="hidden" name="id_plan" value="4">
                                    <input type="hidden" name="id_modalidad" value="1">
                                    <input type="hidden" name="id_nivel" value="2">

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small text-uppercase">Grado / Año</label>
                                            <select name="id_grado" id="id_grado" class="form-select form-select-sm">
                                                <option value="">Seleccione grado...</option>
                                                <?php foreach ($grados_primaria as $g): ?>
                                                    <option value="<?= $g['id_grado'] ?>"><?= $g['nombre_grado'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small text-uppercase">Sección</label>
                                            <select name="id_seccion" id="id_seccion" class="form-select form-select-sm" disabled required>
                                                <option value="">Seleccione grado primero...</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small text-uppercase">Condición</label>
                                            <div class="form-check form-switch p-2 border rounded bg-light" style="height: 31px; display: flex; align-items: center; padding-left: 3em !important;">
                                                <input class="form-check-input" type="checkbox" name="es_repitiente" id="es_repitiente">
                                                <label class="form-check-label fw-bold text-danger small ms-2" for="es_repitiente">
                                                    <i class="bi bi-exclamation-triangle-fill"></i> ¿ES REPITIENTE?
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="text-primary opacity-25">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-uppercase">Correo del Estudiante <span class="text-muted">(Opcional)</span></label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light"><i class="bi bi-envelope-at"></i></span>
                                                <input type="email" name="correo_estudiante" class="form-control" placeholder="ejemplo@correo.com">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-uppercase">Teléfono del Estudiante <span class="text-muted">(Opcional)</span></label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                                <input type="text" name="telefono_estudiante" class="form-control" placeholder="04XX-0000000">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card border-primary mb-4 mt-3 shadow-sm">
                                <div class="card-header bg-primary text-white py-2">
                                    <h6 class="mb-0 small"><i class="bi bi-heart-pulse-fill me-2"></i>DATOS DE SALUD DEL ESTUDIANTE</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-uppercase">Tipo de Sangre</label>
                                            <select name="tipo_sangre" id="tipo_sangre" class="form-select form-select-sm" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($tipos_sangre as $sangre): ?>
                                                    <option value="<?= $sangre['id_sangre'] ?>">
                                                        <?= $sangre['nombre_sangre'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-8 d-flex align-items-end">
                                            <div class="form-check form-switch mb-2 p-2 border rounded bg-light w-100" style="padding-left: 3em !important;">
                                                <input class="form-check-input" type="checkbox" id="tiene_discapacidad" name="tiene_discapacidad" value="Si" onchange="toggleDiscapacidadEst()">
                                                <label class="form-check-label fw-bold small text-uppercase" for="tiene_discapacidad">
                                                    ¿Posee alguna discapacidad / condición especial?
                                                </label>
                                            </div>
                                        </div>

                                        <div id="contenedor_discapacidad" style="display: none;" class="col-md-12">
                                            <label class="form-label small text-danger fw-bold text-uppercase">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Seleccione las condiciones detectadas:
                                            </label>

                                            <div class="card border-primary shadow-sm">
                                                <div class="card-body p-3 bg-light">
                                                    <div class="row g-2">
                                                        <?php
                                                        $current_cat = "";
                                                        foreach ($discapacidades_data as $disc):
                                                            if ($current_cat != $disc['categoria']):
                                                                $current_cat = $disc['categoria'];
                                                                // Cambiado a bg-primary para consistencia
                                                                echo "<div class='col-12 mt-3 mb-1'><span class='badge bg-primary text-uppercase fw-bold' style='font-size: 0.75rem;'>{$current_cat}</span></div>";
                                                            endif;
                                                        ?>
                                                            <div class="col-md-4 col-sm-6">
                                                                <div class="form-check p-2 border rounded-2 m-1 bg-white shadow-sm h-100">
                                                                    <input class="form-check-input ms-0 me-2" type="checkbox"
                                                                        name="discapacidades[]"
                                                                        value="<?= $disc['subtipo_id'] ?>"
                                                                        id="disc_<?= $disc['subtipo_id'] ?>">
                                                                    <label class="form-check-label small d-block" for="disc_<?= $disc['subtipo_id'] ?>"
                                                                        title="<?= htmlspecialchars($disc['descripcion_especifica'] ?? 'Sin descripción') ?>"
                                                                        style="cursor: help;">
                                                                        <strong><?= htmlspecialchars($disc['subtipo_nombre']) ?></strong>
                                                                        <i class="bi bi-info-circle text-primary ms-1"></i>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>

                                                    <div class="mt-3 border-top pt-2">
                                                        <label class="form-label small fw-bold text-uppercase">Observaciones adicionales:</label>
                                                        <textarea name="detalle_discapacidad_obs" class="form-control form-control-sm" rows="2" placeholder="Detalle médico relevante..."></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-1 mt-1"><!-- INICIO ANTROPOMETRÍA ESTUDIANTE - OPCIONAL -->
                                <input class="form-check-input" type="checkbox" role="switch" id="sw_antropometria" onchange="toggleCard('card_antropometria', this)">
                                <label class="form-check-label fw-bold text-success" for="sw_antropometria">
                                    ¿REGISTRAR TALLAS Y MEDIDAS (ANTROPOMETRÍA)?
                                </label>
                            </div>
                            <div class="card border-success shadow-sm" id="card_antropometria" style="display: none;">
                                <div class="card-body bg-light">
                                    <div class="row g-3">
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold">Peso (Kg)</label>
                                            <input type="number" step="0.01" name="peso" class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold">Estatura (Cm)</label>
                                            <input type="number" name="estatura" class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold">Talla Camisa</label>
                                            <input type="text" name="talla_camisa" class="form-control" placeholder="Ej: 12 o S">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold">Talla Pantalón</label>
                                            <input type="text" name="talla_pantalon" class="form-control" placeholder="Ej: 10 o 30">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold">Calzado</label>
                                            <input type="number" name="talla_calzado" class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-bold">Circ. Cefálica</label>
                                            <input type="number" step="0.1" name="circ_cefalica" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- FIN ANTROPOMETRÍA ESTUDIANTE - OPCIONAL -->
                            <div class="card mt-3 shadow-sm border-primary">
                                <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 small"><i class="bi bi-people-fill me-2"></i>GRUPO FAMILIAR ADICIONAL</h6>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="agregar_familiares" onchange="toggleFamiliares()">
                                        <label class="form-check-label small fw-bold text-white" for="agregar_familiares">Registrar Otros Familiares</label>
                                    </div>
                                </div>

                                <div class="card-body" id="seccion_familiares" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover table-bordered align-middle" id="tablafamiliares">
                                            <thead class="table-light small">
                                                <tr class="text-uppercase text-center" style="font-size: 0.75rem;">
                                                    <th style="width: 18%;">Parentesco</th>
                                                    <th style="width: 15%;">Cédula</th>
                                                    <th>Nombres y Apellidos</th>
                                                    <th style="width: 15%;">Teléfono</th>
                                                    <th style="width: 20%;">Correo</th>
                                                    <th style="width: 5%;"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="body_familiares">
                                                <tr>
                                                    <td>
                                                        <select name="f_parentesco[]" class="form-select form-select-sm">
                                                            <option value="">Seleccione...</option>
                                                            <?php foreach ($parentescos as $p): ?>
                                                                <option value="<?= $p['id_parentesco'] ?>"><?= $p['nombre_parentesco'] ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td><input type="text" name="f_cedula[]" class="form-control form-control-sm" placeholder="V-000000"></td>
                                                    <td><input type="text" name="f_nombre[]" class="form-control form-control-sm" placeholder="Nombre completo"></td>
                                                    <td><input type="text" name="f_telefono[]" class="form-control form-control-sm" placeholder="04XX-0000000"></td>
                                                    <td><input type="email" name="f_correo[]" class="form-control form-control-sm" placeholder="correo@ejemplo.com"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="eliminarFila(this)">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <div class="text-start mt-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="agregarFilaFamiliar()">
                                                <i class="bi bi-plus-circle-fill me-1"></i> Agregar otro familiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-5 mb-5">
                                <button type="button"
                                    id="btnFinalizarInscripcion"
                                    class="btn btn-success btn-lg px-5 shadow"
                                    onclick="guardarRegistro()"> <i class="bi bi-save me-2"></i> Finalizar Registro
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
</form>
</div>

<button class="btn btn-dark shadow-lg rounded-pill px-4" type="button" data-bs-toggle="offcanvas" data-bs-target="#panelCupos"
    style="position: fixed; bottom: 30px; right: 30px; z-index: 1050; height: 55px;">
    <i class="bi bi-person-badge-fill me-2"></i>
    <span class="d-none d-md-inline">Disponibilidad de Cupos</span>
    <span class="d-md-none">Cupos</span>
</button>

<div class="offcanvas offcanvas-end border-0 shadow" tabindex="-1" id="panelCupos" aria-labelledby="panelCuposLabel" style="width: 380px;">
    <div class="offcanvas-header bg-dark text-white shadow-sm">
        <h5 class="offcanvas-title fw-bold" id="panelCuposLabel">
            <i class="bi bi-grid-3x3-gap-fill me-2 text-info"></i> CUPOS REALES
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="bg-light p-3 border-bottom d-flex justify-content-around text-center">
            <div>
                <small class="text-muted d-block text-uppercase" style="font-size: 0.6rem;">Total Secciones</small>
                <span id="stat-total-secciones" class="fw-bold">-</span>
            </div>
            <div class="border-start"></div>
            <div>
                <small class="text-muted d-block text-uppercase" style="font-size: 0.6rem;">Cupos Globales</small>
                <span id="stat-total-cupos" class="fw-bold text-success">-</span>
            </div>
        </div>

        <div id="lista-dinamica-cupos" class="p-3">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Consultando base de datos...</p>
            </div>
        </div>
    </div>
</div>


<!-- Modal de confirmación -->
<div class="modal fade" id="morochoModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-warning">
                <h5 class="modal-title">⚠️ Validación de Vínculo Familiar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Se detectó que el representante <strong id="modal_rep"></strong> ya tiene inscritos a los siguientes estudiantes con la misma fecha de nacimiento:</p>
                <div class="alert alert-light border">
                    <ul id="lista_hermanos" class="mb-0"></ul>
                </div>
                <p class="mb-0 text-center fw-bold">¿El estudiante <span id="modal_nuevo_nombre" class="text-primary"></span> es PARTO MÚLTIPLE con ellos?</p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-danger px-4" onclick="confirmarParto(0)">NO</button>
                <button type="button" class="btn btn-success px-4" onclick="confirmarParto(1)">SI</button>
            </div>
        </div>
    </div>
</div>
<!-- FIN Modal de confirmación -->

<?php include_once "../../../includes/footer.php"; ?>

<script>
    // ==========================================
    // 1. VARIABLES GLOBALES E INSTANCIAS
    // ==========================================
    let modalInstancia = null;
    let esPartoMultiple = 0;

    // ==========================================
    // 2. MÓDULO DE UBICACIÓN Y COMBOS ANIDADOS
    // ==========================================

    /**
     * Carga dinámica de combos (Estado -> Municipio -> Parroquia)
     */
    const cargarCombo = (idActual, idSiguiente, tipo, idSiguientePosterior = null) => {
        const actual = document.getElementById(idActual);
        const siguiente = document.getElementById(idSiguiente);
        if (!actual || !siguiente) return;

        actual.addEventListener('change', () => {
            // Si el campo actual está deshabilitado (como en caso de extranjeros), no hacer nada
            if (actual.disabled) return;

            const valorId = actual.value;
            siguiente.innerHTML = '<option value="">Cargando...</option>';
            siguiente.disabled = true;

            if (idSiguientePosterior) {
                const posterior = document.getElementById(idSiguientePosterior);
                if (posterior) {
                    posterior.innerHTML = '<option value="">Seleccione...</option>';
                    posterior.disabled = true;
                }
            }

            if (!valorId) return;
            fetch(`../../controllers/ubicacion/get_ubicacion.php?tipo=${tipo}&id=${valorId}`)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">Seleccione...</option>';
                    if (data && data.length > 0) {
                        data.forEach(item => html += `<option value="${item.id}">${item.nombre}</option>`);
                        siguiente.disabled = false;
                    }
                    siguiente.innerHTML = html;
                }).catch(err => console.error("Error en fetch combos:", err));
        });
    };

    /**
     * Maneja la visibilidad de los campos de nacimiento según el país
     */
    function verificarPaisNacimiento(idPais) {
        const estadoNac = document.getElementById('id_estado_nac');
        const munNac = document.getElementById('id_mun_nac');
        const parNac = document.getElementById('id_parroquia_nac');

        if (!estadoNac || !munNac || !parNac) return;

        // ID 232 corresponde a Venezuela
        if (idPais != "232") {
            // Modo Extranjero: Bloquear y limpiar
            estadoNac.value = "";
            estadoNac.disabled = true;
            estadoNac.required = false;

            munNac.innerHTML = '<option value="0">N/A (Extranjero)</option>';
            munNac.disabled = true;
            munNac.required = false;

            parNac.innerHTML = '<option value="0">N/A (Extranjero)</option>';
            parNac.disabled = true;
            parNac.required = false;
        } else {
            // Modo Nacional: Habilitar
            estadoNac.disabled = false;
            estadoNac.required = true;

            // Solo reseteamos si el valor actual es el de extranjero
            if (munNac.value === "0") {
                munNac.innerHTML = '<option value="">Esperando estado...</option>';
                parNac.innerHTML = '<option value="">Esperando municipio...</option>';
            }
        }
    }

    // ==========================================
    // 3. LÓGICA DE IDENTIDAD Y CÉDULA ESCOLAR
    // ==========================================

    function gestionarIdentidad() {
        const radioNo = document.getElementById('ci_no');
        const inputCI = document.getElementById('cedula_es');
        const selectTipo = document.getElementById('tipo_doc_es');
        const label = document.getElementById('label_id_est');
        const msg = document.getElementById('msg_validacion');

        if (!radioNo || !inputCI) return;

        const esEscolar = radioNo.checked;

        if (label) label.innerText = esEscolar ? "Cédula Escolar (Generada)" : "Número de Identificación (C.I. / Pasaporte)";

        inputCI.readOnly = esEscolar;
        inputCI.placeholder = esEscolar ? "Generando código..." : "Ingrese identificación";
        if (esEscolar) inputCI.classList.add('bg-light');
        else inputCI.classList.remove('bg-light');

        if (selectTipo) {
            selectTipo.disabled = esEscolar;
            if (esEscolar) {
                selectTipo.value = "V";
                selectTipo.classList.add('bg-light');
            } else {
                selectTipo.classList.remove('bg-light');
            }
        }

        if (msg) msg.innerHTML = esEscolar ? '<i class="bi bi-info-circle-fill me-1"></i> Generación automática con datos del representante.' : "";

        if (esEscolar) procesarLogicaCedula();
    }

    function procesarLogicaCedula() {
        const radioNo = document.getElementById('ci_no');
        const elInputCedula = document.getElementById('cedula_es');
        const elCiRep = document.getElementById('cedula_rep');
        const elFecha = document.getElementById('fecha_nacimiento');
        const elPeriodo = document.getElementsByName('periodo_escolar')[0];

        if (!radioNo || !radioNo.checked || !elInputCedula || !elCiRep || !elFecha) return;

        const ciRep = elCiRep.value.trim();
        const fecha = elFecha.value;
        const periodo = elPeriodo ? elPeriodo.value : "";

        if (ciRep && fecha.length === 10) {
            const ciPadded = ciRep.padStart(8, '0');
            const anioCorto = fecha.substring(2, 4);
            const url = `../../controllers/estudiante/validar_estudiante.php?cedula_rep=${ciRep}&fecha_nac=${fecha}&periodo=${periodo}`;

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        const prefijoFinal = data.prefijo || 1;
                        elInputCedula.value = prefijoFinal + anioCorto + ciPadded;
                        esPartoMultiple = data.pacto_multiple ? 1 : 0;
                    }
                })
                .catch(err => {
                    console.error("Error al generar CE:", err);
                    elInputCedula.value = "1" + anioCorto + ciPadded;
                });
        } else {
            elInputCedula.value = "";
        }
    }

    // ==========================================
    // 4. FUNCIONES DE INTERFAZ Y UI (Validaciones)
    // ==========================================

    function validarLogicaFechas() {
        const elFRep = document.getElementsByName('fecha_nac_rep')[0];
        const elFEst = document.getElementById('fecha_nacimiento');
        const elPar = document.getElementsByName('parentesco_rep')[0];
        const elMod = document.getElementById('id_modalidad');

        if (!elFRep || !elFEst || !elPar || !elMod) return true;

        const fechaNacRep = new Date(elFRep.value);
        const fechaNacEst = new Date(elFEst.value);
        const idParentesco = elPar.value;
        const idModalidad = elMod.value;

        if (isNaN(fechaNacRep) || isNaN(fechaNacEst)) return true;

        const edadEstudiante = new Date().getFullYear() - fechaNacEst.getFullYear();

        if (idModalidad == "2" && idParentesco == "5" && edadEstudiante < 18) {
            Swal.fire('Validación', 'En modalidad Adultos, debe ser mayor de 18 años para auto-representarse.', 'error');
            return false;
        }

        if (idParentesco != "5" && fechaNacRep >= fechaNacEst) {
            Swal.fire('Fecha Inválida', 'El representante debe ser mayor que el estudiante.', 'error');
            return false;
        }

        return true;
    }

    function copiarDireccionRepresentante() {
        const check = document.getElementById('copiar_direccion_check');
        if (!check) return;

        const campos = [{
                orig: 'id_estado_rep',
                dest: 'id_estado_hab'
            },
            {
                orig: 'id_municipio_rep',
                dest: 'id_mun_hab'
            },
            {
                orig: 'id_parroquia_rep',
                dest: 'id_parroquia_hab'
            },
            {
                orig: 'direccion_detalle_rep',
                dest: 'direccion_hab_est'
            }
        ];

        campos.forEach(campo => {
            const elOrig = document.getElementById(campo.orig);
            const elDest = document.getElementById(campo.dest);
            if (!elOrig || !elDest) return;

            if (check.checked) {
                if (elOrig.tagName === 'SELECT') {
                    elDest.innerHTML = elOrig.innerHTML;
                    elDest.value = elOrig.value;
                    elDest.style.pointerEvents = "none";
                } else {
                    elDest.value = elOrig.value;
                    elDest.readOnly = true;
                }
                elDest.classList.add('bg-light');
            } else {
                elDest.readOnly = false;
                elDest.style.pointerEvents = "auto";
                elDest.classList.remove('bg-light');
                elDest.value = "";
            }
        });
    }

    function toggleDiscapacidadEst() {
        const check = document.getElementById('tiene_discapacidad');
        const cont = document.getElementById('contenedor_discapacidad');
        if (check && cont) cont.style.display = check.checked ? 'block' : 'none';
    }

    function toggleFamiliares() {
        const check = document.getElementById('agregar_familiares');
        const sec = document.getElementById('seccion_familiares');
        if (check && sec) sec.style.display = check.checked ? 'block' : 'none';
    }

    // ==========================================
    // 5. MANEJO DE ARCHIVOS Y TABLAS
    // ==========================================

    function agregarFilaFamiliar() {
        const tbody = document.getElementById('body_familiares');
        if (!tbody) return;
        const nuevaFila = tbody.querySelector('tr').cloneNode(true);
        nuevaFila.querySelectorAll('input').forEach(i => i.value = '');
        tbody.appendChild(nuevaFila);
    }

    function eliminarFila(boton) {
        const filas = document.getElementById('body_familiares').querySelectorAll('tr');
        if (filas.length > 1) boton.closest('tr').remove();
    }

    function visualizarArchivo(input, idDestino, esPdf = false) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            const archivo = input.files[0];

            reader.onload = function(e) {
                const elemento = document.getElementById(idDestino);

                if (!elemento) {
                    console.error("No se encontró el elemento con ID: " + idDestino);
                    return;
                }

                if (!esPdf) {
                    // LÓGICA PARA FOTOS (Imagen del Representante o Estudiante)
                    elemento.src = e.target.result;
                    elemento.style.display = 'block';

                    // Ocultar el placeholder (el icono gris) si existe
                    const placeholder = elemento.nextElementSibling;
                    if (placeholder && placeholder.id.includes('placeholder')) {
                        placeholder.style.display = 'none';
                    }
                } else {
                    // LÓGICA PARA PDF (Cédula o Partida de Nacimiento)
                    // Buscamos el texto dentro del contenedor para poner el nombre del archivo
                    const textoPdf = elemento.querySelector('p');
                    if (textoPdf) {
                        textoPdf.innerText = archivo.name;
                        textoPdf.classList.remove('text-muted');
                        textoPdf.classList.add('text-success', 'fw-bold');
                    }
                    // Ponemos el borde verde para dar feedback visual
                    elemento.classList.remove('border-muted');
                    elemento.classList.add('border-success', 'bg-light');
                }
            };

            reader.readAsDataURL(archivo);
        }
    }
    // ==========================================
    // 6. OPERACIONES DE DATOS (FETCH)
    // ==========================================

    const buscarRepresentante = () => {
        const ced = document.getElementById('cedula_rep');
        const tipo = document.getElementById('tipo_doc_rep');
        if (!ced || ced.value.length < 6) return;

        fetch(`../../controllers/representante/buscar_representante.php?cedula=${ced.value}&tipo=${tipo.value}`)
            .then(r => r.json())
            .then(data => {
                if (data.encontrado) {
                    Swal.fire({
                        title: '¿Cargar datos?',
                        text: `Representante: ${data.nombres} ${data.apellidos}`,
                        icon: 'question',
                        showCancelButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const map = {
                                'nombre_rep': data.nombres,
                                'apellido_rep': data.apellidos,
                                'sexo_rep': data.sexo,
                                'fecha_nac_rep': data.fecha_nac,
                                'parentesco_rep': data.parentesco,
                                'tel_rep': data.telefono,
                                'correo_rep': data.correo,
                                'direccion_detalle_rep': data.direccion
                            };
                            Object.keys(map).forEach(n => {
                                let el = document.getElementsByName(n)[0];
                                if (el) el.value = map[n];
                            });
                        }
                    });
                }
            });
    };

    function guardarRegistro() {
        if (!validarLogicaFechas()) return;

        const form = document.getElementById('formRegistroCompleto');
        const inputCE = document.getElementById('cedula_es');

        if (document.getElementById('ci_no').checked && (!inputCE.value || inputCE.value.includes('ERROR'))) {
            Swal.fire('Error', 'No se pudo generar la Cédula Escolar válida.', 'error');
            return;
        }

        const formData = new FormData(form);
        formData.append('pacto_multiple', esPartoMultiple);

        Swal.fire({
            title: 'Guardando...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch('../../controllers/representante/guardar_inscripcion.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('¡Éxito!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
    }

    // ==========================================
    // 7. INICIALIZACIÓN
    // ==========================================
    document.addEventListener("DOMContentLoaded", function() {

        // --- Eventos de Identidad ---
        document.querySelectorAll('input[name="tiene_ci"]').forEach(r => r.addEventListener('change', gestionarIdentidad));

        // --- Eventos de País (Nacimiento) ---
        const paisNac = document.getElementById('id_pais_nac');
        if (paisNac) {
            paisNac.addEventListener('change', (e) => verificarPaisNacimiento(e.target.value));
            verificarPaisNacimiento(paisNac.value);
        }

        // --- Recálculo de CE ---
        const cRep = document.getElementById('cedula_rep');
        const cFec = document.getElementById('fecha_nacimiento');
        if (cRep) {
            cRep.addEventListener('input', procesarLogicaCedula);
            cRep.addEventListener('blur', buscarRepresentante);
        }
        if (cFec) cFec.addEventListener('change', procesarLogicaCedula);

        // --- Combos Ubicación ---
        cargarCombo('id_estado_rep', 'id_municipio_rep', 'municipios', 'id_parroquia_rep');
        cargarCombo('id_municipio_rep', 'id_parroquia_rep', 'parroquias');
        cargarCombo('id_estado_hab', 'id_mun_hab', 'municipios', 'id_parroquia_hab');
        cargarCombo('id_mun_hab', 'id_parroquia_hab', 'parroquias');
        cargarCombo('id_estado_nac', 'id_mun_nac', 'municipios', 'id_parroquia_nac');
        cargarCombo('id_mun_nac', 'id_parroquia_nac', 'parroquias');

        // --- Combos Académicos ---
        cargarCombo('id_grado', 'id_seccion', 'secciones');

        // --- Estado Inicial UI ---
        gestionarIdentidad();
        toggleFamiliares();
        toggleDiscapacidadEst();

        // === LÓGICA DE CUPOS (OFFCANVAS) ===
        const panelCupos = document.getElementById('panelCupos');
        if (panelCupos) {
            panelCupos.addEventListener('show.bs.offcanvas', actualizarPanelCupos);
        }

        // === LÓGICA SI REGISTRA DESDE UNA SECCIÓN ESPECÍFICA (URL) ===
        const urlParams = new URLSearchParams(window.location.search);
        const gradoUrl = urlParams.get('id_grado');
        const seccionUrl = urlParams.get('id_seccion');

        if (gradoUrl && seccionUrl) {
            const selectGrado = document.getElementById('id_grado');
            const selectSeccion = document.getElementById('id_seccion');

            if (selectGrado && selectSeccion) {
                selectGrado.value = gradoUrl;
                selectGrado.style.pointerEvents = "none";
                selectGrado.classList.add('bg-light', 'border-primary');
                selectGrado.dispatchEvent(new Event('change'));

                let intentos = 0;
                const verificarCarga = setInterval(() => {
                    intentos++;
                    const opcionExiste = selectSeccion.querySelector(`option[value="${seccionUrl}"]`);
                    if (opcionExiste) {
                        clearInterval(verificarCarga);
                        selectSeccion.value = seccionUrl;
                        selectSeccion.disabled = false;
                        selectSeccion.style.pointerEvents = "none";
                        selectSeccion.classList.add('bg-light', 'border-primary');
                    }
                    if (intentos > 50) clearInterval(verificarCarga);
                }, 100);
            }
        }

        // --- Lógica del botón Siguiente (Paso 1 a Paso 2) ---
        const btnSiguienteRep = document.getElementById('btnSiguienteRep');
        if (btnSiguienteRep) {
            btnSiguienteRep.addEventListener('click', function() {
                // 1. Opcional: Aquí puedes agregar una validación rápida
                const cedula = document.getElementById('cedula_rep').value;
                if (cedula === "") {
                    Swal.fire('Atención', 'Por favor, ingresa al menos la cédula del representante', 'warning');
                    return;
                }

                // 2. Cambiar de pestaña (Bootstrap 5 Tab)
                const tabEstudiante = new bootstrap.Tab(document.getElementById('estudiante-tab'));
                tabEstudiante.show();

                // 3. Hacer scroll hacia arriba para que el usuario vea el inicio del formulario
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    }); // FIN DOMContentLoaded

    // --- FUNCIONES GLOBALES (Fuera del DOMContentLoaded) ---

    function seleccionarDesdePanel(idGrado, idSeccion) {
        const selectGrado = document.getElementById('id_grado');
        const selectSeccion = document.getElementById('id_seccion');

        if (selectGrado && selectSeccion) {
            selectGrado.value = idGrado;
            selectGrado.dispatchEvent(new Event('change'));

            setTimeout(() => {
                selectSeccion.value = idSeccion;
                const panel = bootstrap.Offcanvas.getInstance(document.getElementById('panelCupos'));
                if (panel) panel.hide();

                // Feedback visual
                selectGrado.classList.add('is-valid');
                selectSeccion.classList.add('is-valid');
            }, 500);
        }
    }

    function actualizarPanelCupos() {
        const contenedor = document.getElementById('lista-dinamica-cupos');
        fetch('/matricula/modulos/controllers/secciones/obtener_cupos_disponibles.php')
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    contenedor.innerHTML = '<div class="alert alert-warning">No hay secciones configuradas.</div>';
                    return;
                }

                let html = '';
                let totalLibres = 0;

                data.forEach(sec => {
                    const libres = sec.capacidad_max - sec.total_inscritos;
                    totalLibres += libres;

                    let badgeClass = 'bg-success';
                    let progressClass = 'bg-success';
                    if (libres <= 0) {
                        badgeClass = 'bg-danger';
                        progressClass = 'bg-danger';
                    } else if (libres <= 3) {
                        badgeClass = 'bg-warning text-dark';
                        progressClass = 'bg-warning';
                    }

                    const porcentaje = (sec.total_inscritos / sec.capacidad_max) * 100;

                    // Añadimos el onclick para la selección automática
                    const clickable = libres > 0 ? `onclick="seleccionarDesdePanel(${sec.id_grado}, ${sec.id_seccion})"` : '';
                    const style = libres > 0 ? 'cursor: pointer;' : 'opacity: 0.6; cursor: not-allowed;';

                    html += `
                <div class="card mb-3 border-0 shadow-sm" ${clickable} style="${style}">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 fw-bold text-uppercase" style="font-size: 0.85rem;">
                                ${sec.nombre_grado} <span class="text-primary">"${sec.letra}"</span>
                            </h6>
                            <span class="badge ${badgeClass} border">${libres} Libres</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>Turno: ${sec.turno}</span>
                            <span>${sec.total_inscritos} / ${sec.capacidad_max}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${porcentaje}%"></div>
                        </div>
                    </div>
                </div>`;
                });

                contenedor.innerHTML = html;
                document.getElementById('stat-total-secciones').innerText = data.length;
                document.getElementById('stat-total-cupos').innerText = totalLibres;
            })
            .catch(err => {
                contenedor.innerHTML = '<div class="alert alert-danger">Error al cargar datos.</div>';
            });
    }
</script>