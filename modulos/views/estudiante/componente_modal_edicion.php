<div class="modal fade" id="modalEditarEstudiante" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-dark text-white" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" id="modalEditarLabel">
                    <i class="bi bi-pencil-square me-2"></i>Editar Expediente del Estudiante
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditarEstudiante">
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_id_estudiante" name="edit_id_estudiante">
                    
                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">1. Información Personal</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nombres</label>
                            <input type="text" class="form-control rounded-pill" id="edit_nombre" name="edit_nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Apellidos</label>
                            <input type="text" class="form-control rounded-pill" id="edit_apellido" name="edit_apellido" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Cédula Escolar (Morocho)</label>
                            <div class="input-group">
                                <input type="text" class="form-control rounded-start-pill" id="edit_cedula_escolar" name="edit_cedula_escolar">
                                <button class="btn btn-primary rounded-end-pill" type="button" onclick="generarCedulaGemelo()" title="Calcular según regla de gemelos">
                                    <i class="bi bi-people-fill"></i>
                                </button>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">Prefijo: 1 (Único), 2 (2do Gemelo)...</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Estatus</label>
                            <select class="form-select rounded-pill border-warning" id="edit_estatus" name="edit_estatus">
                                <option value="Pre-inscrito">Pre-inscrito</option>
                                <option value="Inscrito">Inscrito</option>
                                <option value="Retirado">Retirado</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="text-success fw-bold mb-3 border-bottom pb-2">2. Ubicación Académica</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Nivel</label>
                            <select class="form-select rounded-pill" id="edit_nivel" name="edit_nivel">
                                <option value="">-- Seleccione --</option>
                                <?php foreach($niveles_edit as $n): ?>
                                    <option value="<?= $n['id_nivel'] ?>"><?= $n['nombre_nivel'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Grado</label>
                            <select class="form-select rounded-pill" id="edit_grado" name="edit_grado"></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Sección</label>
                            <select class="form-select rounded-pill" id="edit_seccion" name="edit_seccion"></select>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light" style="border-radius: 0 0 20px 20px;">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * 1. Cargar Datos en la Modal (Ajustado para usar IDs de Nivel)
 */
async function abrirEdicion(id) {
    console.log("Iniciando edición para ID:", id);
    try {
        const response = await fetch(`../../controllers/estudiante/obtener_estudiante.php?id=${id}`);
        const data = await response.json();

        if (data.error) throw new Error(data.error);

        // Determinamos el ID del nivel para que el combo lo reconozca
        // Usamos el alias id_nivel que viene del JOIN en el PHP
        const nivelID = data.id_nivel || data.id_nivel_para_combo;

        // Llenado de campos básicos
        const campos = {
            'edit_id_estudiante': data.id_estudiante,
            'edit_nombre': data.nombre,
            'edit_apellido': data.apellido,
            'edit_cedula_escolar': data.cedula_escolar,
            'edit_estatus': data.estatus,
            'edit_nivel': nivelID // <--- Aquí usamos el ID numérico
        };

        for (const [idEl, valor] of Object.entries(campos)) {
            const el = document.getElementById(idEl);
            if (el) el.value = valor || '';
        }

        // --- CARGA EN CASCADA ---
        if (nivelID) {
            // 1. Esperamos a que carguen los grados usando el ID del nivel
            await actualizarCombo('grado', 'grados', nivelID, data.fk_grado);
            
            // 2. Una vez que el combo de grado tiene opciones, cargamos secciones
            if (data.fk_grado) {
                await actualizarCombo('seccion', 'secciones', data.fk_grado, data.id_seccion);
            }
        }

        // Mostrar Modal
        const modalEl = document.getElementById('modalEditarEstudiante');
        const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        myModal.show();

    } catch (error) {
        console.error("Error en abrirEdicion:", error);
        Swal.fire('Error', 'No se pudieron cargar los datos: ' + error.message, 'error');
    }
}

/**
 * 2. Filtros Dinámicos (Se mantiene igual, funciona por Promesas)
 */
function actualizarCombo(targetId, tipo, parentId, valorSeleccionado = null) {
    const combo = document.getElementById('edit_' + targetId);
    if (!combo || !parentId) return Promise.resolve();

    return fetch(`../../controllers/academico/obtener_filtros.php?tipo=${tipo}&id=${parentId}`)
        .then(res => res.json())
        .then(data => {
            combo.innerHTML = `<option value="">-- Seleccione --</option>`;
            data.forEach(item => {
                const id = item.id_grado || item.id_seccion;
                let texto = item.nombre_grado || `${item.letra} (${item.turno})`;
                let disabled = "";

                if (tipo === 'secciones') {
                    const disponibles = parseInt(item.capacidad_max) - parseInt(item.ocupados);
                    texto += ` - [Cupos: ${disponibles}]`;
                    if (disponibles <= 0 && id != valorSeleccionado) {
                        disabled = "disabled";
                        texto += " (LLENA)";
                    }
                }

                const option = document.createElement('option');
                option.value = id;
                option.textContent = texto;
                if (id == valorSeleccionado) option.selected = true;
                if (disabled) option.disabled = true;
                combo.appendChild(option);
            });
        });
}

// Eventos de cambio manual (Nivel -> Grado -> Sección)
document.getElementById('edit_nivel').addEventListener('change', function() {
    actualizarCombo('grado', 'grados', this.value);
    document.getElementById('edit_seccion').innerHTML = '<option value="">-- Elija Grado --</option>';
});

document.getElementById('edit_grado').addEventListener('change', function() {
    actualizarCombo('seccion', 'secciones', this.value);
});

// 3. Función de Gemelos
function generarCedulaGemelo() {
    const id = document.getElementById('edit_id_estudiante').value;
    if(!id) return;

    fetch(`../../controllers/estudiante/verificar_gemelos.php?id_estudiante=${id}`)
        .then(res => res.json())
        .then(d => {
            if(d.error) {
                Swal.fire('Error', d.error, 'error');
                return;
            }
            const nuevaCedula = `${d.siguiente_prefijo}${d.fecha_nacimiento_formateada}${d.cedula_representante}`;
            document.getElementById('edit_cedula_escolar').value = nuevaCedula;
            Swal.fire({ 
                title: 'Cédula Generada', 
                text: `Se aplicó la lógica de hermanos: Prefijo ${d.siguiente_prefijo}`, 
                icon: 'success' 
            });
        });
}

// 4. Guardado AJAX
document.getElementById('formEditarEstudiante').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('../../controllers/estudiante/actualizar_estudiante.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "success") {
            Swal.fire('¡Actualizado!', 'Expediente guardado correctamente.', 'success')
                .then(() => location.reload());
        } else {
            Swal.fire('Error', 'Error al guardar: ' + data, 'error');
        }
    });
});
</script>