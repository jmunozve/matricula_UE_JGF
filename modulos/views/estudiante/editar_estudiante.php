<?php

/**
 * Ubicación: F:\xampp\htdocs\matricula\modulos\views\estudiante\editar_estudiante_modal.php
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../../../includes/db.php";

$id_estudiante = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id_estudiante) {
    die("<div class='alert alert-danger'>ID de estudiante no proporcionado.</div>");
}

try {
    $pdo = Conexion::abrir();

    // 1. Consulta principal
    $sql = "SELECT v.*, 
                   r.nombre_rep, r.apellido_rep, r.cedula_rep,
                   n.id_modalidad 
            FROM vista_estudiantes_completa v
            LEFT JOIN representantes r ON v.id_representante = r.id_representante 
            LEFT JOIN niveles_estudio n ON v.nombre_nivel = n.nombre_nivel 
            WHERE v.id_estudiante = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_estudiante]);
    $est = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$est) {
        die("<div class='alert alert-warning'>Estudiante no encontrado.</div>");
    }

    // 2. Familiares
    $stmt_fam = $pdo->prepare("SELECT * FROM estudiante_familiares 
                                WHERE id_estudiante = ? 
                                ORDER BY es_principal DESC, nombres_fam ASC");
    $stmt_fam->execute([$id_estudiante]);
    $fams_existentes = $stmt_fam->fetchAll(PDO::FETCH_ASSOC);

    // 3. Catálogos generales
    $cat_parentescos = $pdo->query("SELECT id_parentesco, nombre_parentesco FROM parentescos")->fetchAll(PDO::FETCH_ASSOC);
    $modalidades     = $pdo->query("SELECT id_modalidad, nombre_modalidad FROM modalidades")->fetchAll(PDO::FETCH_ASSOC);
    $paises          = $pdo->query("SELECT id_pais, nombre_pais FROM paises ORDER BY nombre_pais ASC")->fetchAll(PDO::FETCH_ASSOC);
    $estados_vzla    = $pdo->query("SELECT id_estado, nombre_estado FROM estados ORDER BY nombre_estado ASC")->fetchAll(PDO::FETCH_ASSOC);
    $tipos_sangre    = $pdo->query("SELECT id_sangre, nombre_sangre FROM tipos_sangre ORDER BY id_sangre ASC")->fetchAll(PDO::FETCH_ASSOC);

    // 4. Grados disponibles (Para el select "Grado a Inscribir")
    $grados_primaria = $pdo->query("SELECT id_grado, nombre_grado FROM grados WHERE id_plan = 4 ORDER BY id_grado ASC")->fetchAll(PDO::FETCH_ASSOC);

    // 5. Secciones disponibles según el grado actual del estudiante
    $secciones_disponibles = [];
    $grado_id_actual = $est['id_grado'] ?? null;

    if ($grado_id_actual) {
        // Seleccionamos id_seccion y letra (A, B, etc) de tu tabla secciones
        $stmt_sec = $pdo->prepare("SELECT id_seccion, letra, turno FROM secciones 
                                   WHERE id_grado = ? AND estatus = 'Activo'");
        $stmt_sec->execute([$grado_id_actual]);
        $secciones_disponibles = $stmt_sec->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    die("<div class='alert alert-danger'>Error de Base de Datos: " . $e->getMessage() . "</div>");
}
?>
<div class="modal-header bg-dark text-white">
    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Estudiante: <?= $est['nombre_es'] ?></h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body bg-light">
    <div class="alert alert-info d-flex justify-content-between align-items-center py-2">
        <span><i class="bi bi-info-circle me-2"></i>Habilite el interruptor para modificar los datos.</span>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="m_switchEditar" onchange="m_toggleEdicion()">
            <label class="form-check-label fw-bold" for="m_switchEditar">EDITAR</label>
        </div>
    </div>

    <form id="m_formEditarEstudiante">
        <input type="hidden" name="id_estudiante" value="<?= $est['id_estudiante'] ?>">
        <input type="hidden" id="m_cedula_rep_hidden" value="<?= $est['cedula_rep'] ?>">

        <ul class="nav nav-tabs mb-3" id="editTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-basicos" type="button">Básicos</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ubicacion" type="button">Ubicación</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-salud" type="button">Salud / Otros</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documentos" type="button">Documentos</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-academico" type="button">Académico</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-familiares" type="button">Familiares / Contactos</button></li>
        </ul>

        <div class="tab-content border bg-white p-3 rounded shadow-sm">

            <div class="tab-pane fade show active" id="tab-basicos">
                <div class="row g-3">
                    <div class="col-md-4 text-center">
                        <?php
                        $base_url = "/matricula";
                        $ruta_foto = !empty($est['foto_carnet'])
                            ? $base_url . '/uploads/fotos_est/' . $est['foto_carnet']
                            : $base_url . '/public/assets/img/default-user.png';
                        ?>
                        <img id="m_pre_foto" src="<?= $ruta_foto ?>" class="img-thumbnail mb-2" style="height: 150px; width: 130px; object-fit: cover;" onerror="this.src='/matricula/public/assets/img/default-user.png';">
                        <input type="file" name="foto_carnet" id="m_input_foto" class="d-none"
                            onchange="visualizarArchivo(this, 'm_pre_foto', false)">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 m_campo" onclick="document.getElementById('m_input_foto').click()" disabled>Cambiar Foto</button>
                    </div>
                    <div class="col-md-8">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="small fw-bold">Nombres</label>
                                <input type="text" name="nombre_estudiante" class="form-control form-control-sm m_campo" value="<?= $est['nombre_es'] ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold">Apellidos</label>
                                <input type="text" name="apellido_estudiante" class="form-control form-control-sm m_campo" value="<?= $est['apellido_es'] ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold">Tipo Doc.</label>
                                <select name="tipo_doc_es" id="m_selector_tipo" class="form-select form-select-sm m_campo" onchange="m_toggleCedula()" disabled>
                                    <option value="V" <?= $est['tipo_doc_es'] == 'V' ? 'selected' : '' ?>>V</option>
                                    <option value="E" <?= $est['tipo_doc_es'] == 'E' ? 'selected' : '' ?>>E</option>
                                    <option value="CE" <?= $est['tipo_doc_es'] == 'CE' ? 'selected' : '' ?>>CÉDULA ESCOLAR</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="small fw-bold">Nro. Identidad</label>
                                <input type="text" name="cedula_estudiante" id="m_input_cedula" class="form-control form-control-sm m_campo" value="<?= ($est['tipo_doc_es'] == 'CE') ? $est['cedula_escolar'] : $est['cedula_es'] ?>" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Fecha Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="m_fecha_nac" class="form-control form-control-sm m_campo" value="<?= $est['fecha_nacimiento'] ?>" onchange="m_generarCE()" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Sexo</label>
                        <select name="sexo" class="form-select form-select-sm m_campo" disabled>
                            <option value="1" <?= $est['sexo_es'] == 1 ? 'selected' : '' ?>>Masculino</option>
                            <option value="2" <?= $est['sexo_es'] == 2 ? 'selected' : '' ?>>Femenino</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">¿Es Gemelo? (Orden)</label>
                        <select name="orden_gemelo" id="m_orden_gemelo" class="form-select form-select-sm m_campo" onchange="m_generarCE()" disabled>
                            <option value="0" <?= $est['pacto_multiple'] == 0 ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= $est['pacto_multiple'] == 1 ? 'selected' : '' ?>>1°</option>
                            <option value="2" <?= $est['pacto_multiple'] == 2 ? 'selected' : '' ?>>2°</option>
                            <option value="3" <?= $est['pacto_multiple'] == 3 ? 'selected' : '' ?>>3°</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-ubicacion">
                <h6 class="fw-bold border-bottom pb-2">Lugar de Nacimiento</h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="small fw-bold">País</label>
                        <select name="id_pais_es" class="form-select form-select-sm m_campo" disabled>
                            <?php foreach ($paises as $p): ?>
                                <option value="<?= $p['id_pais'] ?>" <?= $est['id_pais_es'] == $p['id_pais'] ? 'selected' : '' ?>><?= $p['nombre_pais'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Estado</label>
                        <select name="id_estado_nac" id="m_estado_nac" class="form-select form-select-sm m_campo" disabled>
                            <option value="">Seleccione...</option>
                            <?php foreach ($estados_vzla as $edo): ?>
                                <option value="<?= $edo['id_estado'] ?>" <?= $est['id_estado_nac'] == $edo['id_estado'] ? 'selected' : '' ?>><?= $edo['nombre_estado'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Municipio</label>
                        <select name="id_mun_nac" id="m_municipio_nac" class="form-select form-select-sm m_campo" disabled></select>
                    </div>
                </div>
                <h6 class="fw-bold border-bottom pb-2">Dirección de Habitación</h6>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="small fw-bold">Estado</label>
                        <select name="id_estado_hab" id="m_estado_hab" class="form-select form-select-sm m_campo" disabled>
                            <option value="">Seleccione...</option>
                            <?php foreach ($estados_vzla as $edo): ?>
                                <option value="<?= $edo['id_estado'] ?>" <?= $est['id_estado_hab'] == $edo['id_estado'] ? 'selected' : '' ?>><?= $edo['nombre_estado'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="small fw-bold">Municipio</label><select name="id_mun_hab" id="m_municipio_hab" class="form-select form-select-sm m_campo" disabled></select></div>
                    <div class="col-md-4"><label class="small fw-bold">Parroquia</label><select name="id_parroquia_hab" id="m_parroquia_hab" class="form-select form-select-sm m_campo" disabled></select></div>
                    <div class="col-12"><label class="small fw-bold">Dirección Detallada</label><textarea name="direccion_detalle" class="form-control form-control-sm m_campo" disabled><?= $est['direccion_detalle'] ?></textarea></div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-salud">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="small fw-bold">Tipo Sangre</label>
                        <select name="tipo_sangre" class="form-select form-select-sm m_campo" disabled>
                            <option value="">Seleccione...</option>
                            <?php foreach ($tipos_sangre as $ts): ?>
                                <option value="<?= $ts['id_sangre'] ?>" <?= ($est['tipo_sangre'] == $ts['id_sangre']) ? 'selected' : '' ?>><?= $ts['nombre_sangre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Discapacidad</label>
                        <select name="tiene_discapacidad" class="form-select form-select-sm m_campo" disabled>
                            <option value="No" <?= $est['tiene_discapacidad'] == 'No' ? 'selected' : '' ?>>No</option>
                            <option value="Si" <?= $est['tiene_discapacidad'] == 'Si' ? 'selected' : '' ?>>Si</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="small fw-bold">Detalle Discapacidad</label>
                        <input type="text" name="detalle_discapacidad" class="form-control form-control-sm m_campo" value="<?= $est['detalle_discapacidad'] ?>" disabled>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-documentos">
                <div class="alert alert-light border mb-3">
                    <p class="small mb-0 text-muted"><i class="bi bi-info-circle-fill text-primary me-1"></i> Formatos: PDF, JPG, PNG (Máx. 2MB).</p>
                </div>
                <div class="row g-4">
                    <?php
                    $docs = ['doc_partida' => 'Partida de Nacimiento', 'doc_cedula'  => 'Cédula de Identidad', 'doc_boleta'  => 'Boleta', 'doc_sano' => 'Certificado Sano', 'doc_vacunas' => 'Vacunación'];
                    foreach ($docs as $campo => $label):
                        $archivoExiste = !empty($est[$campo]);
                        $rutaArchivo = "/matricula/uploads/documentos_est/" . $est[$campo];
                    ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm bg-light p-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="small fw-bold"><?= $label ?> <?= $archivoExiste ? '<i class="bi bi-check-circle-fill text-success"></i>' : '' ?></label>
                                    <?php if ($archivoExiste): ?><a href="<?= $rutaArchivo ?>" target="_blank" class="small text-decoration-none">Ver actual</a><?php endif; ?>
                                </div>
                                <input type="file" name="<?= $campo ?>" class="form-control form-control-sm m_campo" disabled>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>


            <div class="tab-pane fade" id="tab-academico">
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="d-flex align-items-center p-3 bg-light border-start border-4 border-primary rounded shadow-sm">
                            <div class="ms-3">
                                <p class="small text-muted mb-0 text-uppercase fw-bold" style="font-size: 0.7rem;">Ubicación Actual (Año Escolar en Curso)</p>
                                <h6 class="mb-0 fw-bold">
                                    <?= htmlspecialchars($est['nombre_nivel'] ?? 'N/A') ?> >
                                    <?= htmlspecialchars($est['nombre_grado'] ?? 'N/A') ?> >
                                    <span class="text-primary">
                                        Sección Actual: <?= htmlspecialchars($est['letra'] ?? ($est['nombre_seccion'] ?? 'N/A')) ?>
                                        <?= !empty($est['turno']) ? "(" . htmlspecialchars($est['turno']) . ")" : '' ?>
                                    </span>
                                </h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="small fw-bold text-uppercase">Repitiente o Regular</label>
                        <select name="es_repitiente" class="form-select form-select-sm m_campo" disabled>
                            <option value="0" <?= (isset($est['es_repitiente']) && $est['es_repitiente'] == 0) ? 'selected' : '' ?>>REGULAR</option>
                            <option value="1" <?= (isset($est['es_repitiente']) && $est['es_repitiente'] == 1) ? 'selected' : '' ?>>REPITIENTE</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="small fw-bold text-uppercase">Grado a Inscribir</label>
                        <select id="m_grado_acad" name="id_grado" class="form-select form-select-sm m_campo"
                            onchange="cargarSeccionesModal(this.value)" disabled>
                            <option value="">-- Seleccione Grado --</option>
                            <?php if (!empty($grados_primaria)): ?>
                                <?php foreach ($grados_primaria as $g): ?>
                                    <option value="<?= $g['id_grado'] ?>" <?= (isset($est['id_grado']) && $est['id_grado'] == $g['id_grado']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g['nombre_grado']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">Error: No hay grados activos</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="small fw-bold text-uppercase">Sección Destino</label>
                        <div class="input-group input-group-sm">
                            <select id="m_seccion_acad" name="id_seccion" class="form-select form-select-sm m_campo" disabled>
                                <option value="">-- Seleccione --</option>
                                <?php if (!empty($secciones_disponibles)): ?>
                                    <?php foreach ($secciones_disponibles as $s): ?>
                                        <?php
                                        // Prioriza la 'letra' que tienes en tu tabla secciones
                                        $txtSeccion = !empty($s['letra']) ? "SECCIÓN " . $s['letra'] : ($s['nombre_seccion'] ?? 'S/N');
                                        $txtTurno = !empty($s['turno']) ? " (" . $s['turno'] . ")" : "";
                                        ?>
                                        <option value="<?= $s['id_seccion'] ?>" <?= (isset($est['id_seccion']) && $est['id_seccion'] == $s['id_seccion']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($txtSeccion . $txtTurno) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="<?= ($est['id_seccion'] ?? '') ?>" selected>
                                        <?= "SECCIÓN " . ($est['letra'] ?? ($est['nombre_seccion'] ?? 'N/A')) ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                            <span class="input-group-text d-none" id="m_spinner_secciones">
                                <span class="spinner-border spinner-border-sm text-primary"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-familiares">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-people-fill me-2"></i>Grupo Familiar y Contactos
                    </h6>
                    <button type="button" class="btn btn-sm btn-primary m_campo" onclick="m_agregarFilaFamiliar()" disabled>
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover border align-middle">
                        <thead class="bg-light small sticky-top">
                            <tr>
                                <th class="text-center" style="width: 50px;"><i class="bi bi-star-fill text-warning"></i></th>
                                <th style="width: 140px;">Parentesco</th>
                                <th style="width: 110px;">Cédula</th>
                                <th>Nombres</th>
                                <th style="width: 120px;">Teléfono</th>
                                <th>Correo Electrónico</th>
                                <th class="text-center" style="width: 40px;"><i class="bi bi-trash"></i></th>
                            </tr>
                        </thead>
                        <tbody id="m_tbodyFamiliares">
                            <?php if (!empty($fams_existentes)): ?>
                                <?php foreach ($fams_existentes as $f):
                                    $es_principal = ($f['es_principal'] == 1);
                                ?>
                                    <tr class="<?= $es_principal ? 'table-primary' : '' ?>">
                                        <td class="text-center">
                                            <div class="position-relative d-flex justify-content-center">
                                                <i class="bi bi-pencil-square text-primary m_icono_edicion d-none" title="Editando datos..."></i>
                                                <div class="m_icono_estatico">
                                                    <?php if ($es_principal): ?>
                                                        <i class="bi bi-star-fill text-warning" title="Representante Legal"></i>
                                                        <input type="hidden" name="es_principal_fam[]" value="1">
                                                    <?php else: ?>
                                                        <i class="bi bi-person text-muted"></i>
                                                        <input type="hidden" name="es_principal_fam[]" value="0">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="hidden" name="id_est_fam[]" value="<?= $f['id_est_fam'] ?>">
                                            <select name="id_parentesco_fam[]" class="form-select form-select-sm m_campo" disabled>
                                                <?php foreach ($cat_parentescos as $cp): ?>
                                                    <option value="<?= $cp['id_parentesco'] ?>" <?= ($f['id_parentesco'] == $cp['id_parentesco']) ? 'selected' : '' ?>>
                                                        <?= $cp['nombre_parentesco'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="cedula_fam[]" class="form-control form-control-sm fw-bold bg-light" value="<?= $f['cedula_fam'] ?>" readonly>
                                        </td>
                                        <td>
                                            <input type="text" name="nombres_fam[]" class="form-control form-control-sm m_campo text-uppercase" value="<?= $f['nombres_fam'] ?>" disabled>
                                        </td>
                                        <td>
                                            <input type="text" name="telefono_fam[]" class="form-control form-control-sm m_campo" value="<?= $f['telefono_fam'] ?>" placeholder="04XX-XXXXXXX" disabled>
                                        </td>
                                        <td>
                                            <input type="email" name="correo_fam[]" class="form-control form-control-sm m_campo" value="<?= $f['correo_fam'] ?>" placeholder="correo@ejemplo.com" disabled>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!$es_principal): ?>
                                                <button type="button" class="btn btn-outline-danger btn-xs m_campo" onclick="m_eliminarFilaFamiliar(this, <?= $f['id_est_fam'] ?>)" disabled>
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-white text-primary border border-primary" style="font-size: 0.6rem;">LEGAL</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="m_fila_vacia">
                                    <td colspan="7" class="text-center py-4 text-muted small">
                                        <i class="bi bi-info-circle me-2"></i> No se encontraron familiares para este estudiante.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div id="m_familiares_eliminados"></div>
            </div>

        </div><!-- FIN DE .tab-content -->
    </form>

</div>

<div class="modal-footer bg-light">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
    <button type="button" id="btnGuardarFicha" class="btn btn-primary">
        Actualizar Datos
    </button>
</div>


<script>
    /**
     * 1. CARGA DE COMBOS DINÁMICOS CORREGIDA
     * Se ajustó la extracción de idVal y nomVal para evitar el error "undefined"
     */
    async function m_cargarCombo(tipo, idPadre, idHijo, selectedId = null) {
        const select = document.getElementById(idHijo);
        if (!select || !idPadre) return;
        try {
            const res = await fetch(`/matricula/modulos/controllers/ubicacion/get_ubicacion.php?tipo=${tipo}&id=${idPadre}`);
            const data = await res.json();

            select.innerHTML = '<option value="">Seleccione...</option>';

            if (data && Array.isArray(data)) {
                data.forEach(d => {
                    // Esto evita el "undefined": busca todas las posibles columnas de ID y Nombre
                    const idVal = d.id || d.id_municipio || d.id_parroquia || d.id_estado;
                    const nomVal = d.nombre || d.nombre_municipio || d.nombre_parroquia || d.nombre_estado;

                    if (idVal !== undefined && nomVal !== undefined) {
                        const isSelected = (idVal == selectedId) ? 'selected' : '';
                        const option = document.createElement('option');
                        option.value = idVal;
                        if (isSelected) option.selected = true;
                        option.textContent = nomVal;
                        select.appendChild(option);
                    }
                });
            }
        } catch (e) {
            console.error("Error en fetch combos:", e);
        }
    }

    async function cargarSeccionesModal(gradoId, seleccionado = null) {
        if (!gradoId) return;
        const select = document.getElementById('m_seccion_acad');
        if (!select) return;

        // Efecto visual de carga
        select.innerHTML = '<option value="">Cargando secciones...</option>';

        try {
            const res = await fetch(`/matricula/modulos/controllers/ubicacion/get_ubicacion.php?tipo=secciones&id=${gradoId}`);
            const data = await res.json();

            select.innerHTML = '<option value="">-- Seleccione --</option>';

            if (data && Array.isArray(data)) {
                data.forEach(s => {
                    // Usamos los alias estandarizados del PHP corregido
                    const isSel = (s.id == seleccionado) ? 'selected' : '';
                    const option = document.createElement('option');
                    option.value = s.id;
                    if (isSel) option.selected = true;

                    // Forzamos mayúsculas para que se vea uniforme: SECCIÓN A (MAÑANA)
                    option.textContent = (s.nombre || "SIN NOMBRE").toUpperCase();

                    select.appendChild(option);
                });
            }

            if (select.options.length <= 1) {
                select.innerHTML = '<option value="">No hay secciones disponibles</option>';
            }

        } catch (e) {
            console.error("Error en la carga de secciones:", e);
            select.innerHTML = '<option value="">Error al cargar</option>';
        }
    }

    /**
     * 3. INICIALIZACIÓN DE DATOS
     */
    (async function m_init() {
        const e = <?= json_encode($est) ?>;

        // Cargas iniciales
        if (e.id_estado_nac) await m_cargarCombo('municipios', e.id_estado_nac, 'm_municipio_nac', e.id_mun_nac);
        if (e.id_estado_hab) await m_cargarCombo('municipios', e.id_estado_hab, 'm_municipio_hab', e.id_mun_hab);
        if (e.id_mun_hab) await m_cargarCombo('parroquias', e.id_mun_hab, 'm_parroquia_hab', e.id_parroquia_hab);
        if (e.id_grado) await cargarSeccionesModal(e.id_grado, e.id_seccion);

        // Listeners mejorados
        document.getElementById('m_estado_nac')?.addEventListener('change', (ev) => m_cargarCombo('municipios', ev.target.value, 'm_municipio_nac'));
        document.getElementById('m_estado_hab')?.addEventListener('change', (ev) => m_cargarCombo('municipios', ev.target.value, 'm_municipio_hab'));
        document.getElementById('m_municipio_hab')?.addEventListener('change', (ev) => m_cargarCombo('parroquias', ev.target.value, 'm_parroquia_hab'));
        document.getElementById('m_grado_acad')?.addEventListener('change', (ev) => cargarSeccionesModal(ev.target.value));

        window.m_toggleEdicion();
    })();

    /**
     * 4. UTILIDADES DE LA MODAL
     */
    window.m_toggleEdicion = function() {
        const sw = document.getElementById('m_switchEditar');
        if (!sw) return;
        const hab = sw.checked;

        $(".m_campo").prop("disabled", !hab);
        // Sincronizado con el ID del botón en el HTML
        $("#btnGuardarFicha").prop("disabled", !hab);

        const iconosEstaticos = document.querySelectorAll('.m_icono_estatico');
        const iconosEdicion = document.querySelectorAll('.m_icono_edicion');

        iconosEstaticos.forEach(i => hab ? i.classList.add('d-none') : i.classList.remove('d-none'));
        iconosEdicion.forEach(i => hab ? i.classList.remove('d-none') : i.classList.add('d-none'));
    };

    /**
     * 5. GESTIÓN DE FAMILIARES
     */
    window.m_agregarFilaFamiliar = function() {
        const tbody = document.getElementById('m_tbodyFamiliares');
        if (!tbody) return;

        const filaVacia = document.getElementById('m_fila_vacia');
        if (filaVacia) filaVacia.remove();

        const row = document.createElement('tr');
        const parentescos = <?= json_encode($cat_parentescos ?? []) ?>;

        let options = '<option value="">Seleccione...</option>';
        parentescos.forEach(p => {
            options += `<option value="${p.id_parentesco}">${p.nombre_parentesco}</option>`;
        });

        row.innerHTML = `
            <td class="text-center"><i class="bi bi-pencil-square text-primary"></i><input type="hidden" name="es_principal_fam[]" value="0"></td>
            <td><input type="hidden" name="id_est_fam[]" value="NUEVO"><select name="id_parentesco_fam[]" class="form-select form-select-sm m_campo" required>${options}</select></td>
            <td><input type="text" name="cedula_fam[]" class="form-control form-control-sm m_campo fw-bold" placeholder="Cédula"></td>
            <td><input type="text" name="nombres_fam[]" class="form-control form-control-sm m_campo text-uppercase" placeholder="Nombre completo" required></td>
            <td><input type="text" name="telefono_fam[]" class="form-control form-control-sm m_campo" placeholder="Teléfono"></td>
            <td><input type="email" name="correo_fam[]" class="form-control form-control-sm m_campo" placeholder="Correo"></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-xs" onclick="m_eliminarFilaFamiliar(this)"><i class="bi bi-x-lg"></i></button></td>`;

        tbody.appendChild(row);
        window.m_toggleEdicion();
    };

    window.m_eliminarFilaFamiliar = function(btn) {
        const row = btn.closest('tr');
        const idInput = row.querySelector('input[name="id_est_fam[]"]');
        const id = idInput ? idInput.value : "NUEVO";

        if (id !== "NUEVO") {
            const container = document.getElementById('m_familiares_eliminados');
            if (container) {
                container.innerHTML += `<input type="hidden" name="eliminar_familiares[]" value="${id}">`;
            }
        }
        row.remove();
    };

    /**
     * 6. ENVÍO DE DATOS (AJAX)
     */
    document.getElementById('btnGuardarFicha')?.addEventListener('click', async function() {
        const form = document.getElementById('m_formEditarEstudiante');
        if (!form) return;

        const formData = new FormData(form);
        const btn = this;

        btn.disabled = true;
        const textoOriginal = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> PROCESANDO...';

        try {
            const res = await fetch('/matricula/modulos/controllers/estudiante/actualizar_estudiante.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.status === 'success') {
                alert("✅ " + data.message);
                location.reload();
            } else {
                alert("❌ Error: " + data.message);
                btn.disabled = false;
                btn.innerHTML = textoOriginal;
            }
        } catch (e) {
            console.error(e);
            alert("❌ Error crítico de conexión");
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }
    });


function visualizarArchivo(input, idDestino, esPdf = false) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const elemento = document.getElementById(idDestino);
            if (elemento) {
                if (!esPdf) {
                    elemento.src = e.target.result;
                    elemento.style.display = 'block';
                } else {
                    const textoPdf = elemento.querySelector('p');
                    if (textoPdf) {
                        textoPdf.innerText = input.files[0].name;
                        textoPdf.classList.add('text-success', 'fw-bold');
                    }
                }
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>