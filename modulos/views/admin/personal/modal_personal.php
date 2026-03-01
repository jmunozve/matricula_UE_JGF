<div class="modal fade" id="modalPersonal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold" id="modalTitle">
                    <i class="bi bi-person-gear me-2"></i>Datos del Personal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPersonal">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_docente" id="id_docente">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Cédula</label>
                            <input type="text" name="cedula" id="cedula" class="form-control" placeholder="V-00.000.000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Cargo Institucional</label>
                            <select name="cargo" id="cargo" class="form-select" required>
                                <option value="Docente">Docente</option>
                                <option value="Directivo">Directivo</option>
                                <option value="Administrativo">Administrativo</option>
                                <option value="Obrero">Obrero</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Nombres</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Apellidos</label>
                            <input type="text" name="apellido" id="apellido" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small">Especialidad / Área de Trabajo</label>
                            <select name="id_especialidad" id="id_especialidad" class="form-select" required>
                                <option value="">Seleccione una especialidad...</option>
                                <?php 
                                $grupo_actual = "";
                                foreach ($especialidades as $esp): 
                                    // Crear encabezados de grupo (Docente, Administrativo, etc.)
                                    if ($grupo_actual !== $esp['tipo_personal']): 
                                        if ($grupo_actual !== "") echo '</optgroup>';
                                        $grupo_actual = $esp['tipo_personal'];
                                        echo '<optgroup label="' . htmlspecialchars($grupo_actual) . '">';
                                    endif;
                                ?>
                                    <option value="<?= $esp['id_especialidad'] ?>">
                                        <?= htmlspecialchars($esp['nombre_especialidad']) ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if ($grupo_actual !== "") echo '</optgroup>'; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Correo Electrónico</label>
                            <input type="email" name="email" id="email" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>