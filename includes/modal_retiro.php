<div class="modal fade" id="modalRetiroEstudiante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-danger bg-opacity-10 rounded-3 me-3">
                        <i class="bi bi-person-x text-danger fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Retirar Estudiante</h5>
                        <small class="text-muted" id="subtitulo-retiro">Cargando...</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="formRetiroEstudiante">
                    <input type="hidden" name="id_estudiante" id="retiro_id_estudiante">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Fecha de Retiro</label>
                            <input type="date" class="form-control" name="fecha_retiro" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Motivo del Retiro</label>
                            <select class="form-select" name="id_motivo" required>
                                <option value="">-- Seleccione --</option>
                                <option value="1">Traslado a otra institución</option>
                                <option value="2">Cambio de residencia</option>
                                <option value="5">Decisión del representante</option>
                                <option value="6">Otros (especificar en observaciones)</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Observaciones (Opcional)</label>
                            <textarea class="form-control" name="observaciones" rows="2" placeholder="Ej: Se mudo del estado..."></textarea>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger px-4 fw-bold">Confirmar Retiro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Instancia global para la modal
var modalRetiroObj = null;

/**
 * Abre la modal de retiro y carga los datos básicos del alumno
 */
window.abrirModalRetiro = function(id, rutaRaiz) {
    const formRetiro = document.getElementById('formRetiroEstudiante');
    if(formRetiro) formRetiro.reset();
    
    const inputId = document.getElementById('retiro_id_estudiante');
    if(inputId) inputId.value = id;
    
    document.getElementById('subtitulo-retiro').innerText = "Cargando datos...";
    
    // FETCH para traer nombre y apellido
    fetch(`${rutaRaiz}modulos/controllers/estudiante/get_estudiante.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            // Ajustamos según los nombres de columna que tengas en la base de datos
            const nombre = data.nombre_es || data.nombre || "Estudiante";
            const apellido = data.apellido_es || data.apellido || "";
            document.getElementById('subtitulo-retiro').innerText = `${apellido} ${nombre}`;
            
            const el = document.getElementById('modalRetiroEstudiante');
            modalRetiroObj = bootstrap.Modal.getOrCreateInstance(el);
            modalRetiroObj.show();
        })
        .catch(err => {
            console.error("Error al cargar datos:", err);
            Swal.fire('Error', 'No se pudo obtener información del estudiante', 'error');
        });
};

/**
 * Listener para el envío del formulario mediante AJAX
 */
document.addEventListener('submit', function (e) {
    if (e.target && e.target.id === 'formRetiroEstudiante') {
        e.preventDefault();
        const form = e.target;

        Swal.fire({
            title: '¿Confirmar Retiro?',
            text: "Esta acción liberará el cupo y cambiará el estatus del estudiante.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, retirar definitivamente',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const formData = new FormData(form);
                return fetch('/matricula/modulos/controllers/estudiante/procesar_retiro.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) throw new Error('Error en la respuesta del servidor');
                    return res.json();
                })
                .catch(error => {
                    Swal.showValidationMessage(`Error: ${error.message}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value && result.value.status === 'success') {
                    Swal.fire('¡Retirado!', result.value.msg, 'success').then(() => {
                        if(modalRetiroObj) modalRetiroObj.hide();
                        
                        // Refrescar lista de estudiantes si existe la función, si no, recargar página
                        if (typeof window.filtrarContenido === 'function') {
                            window.filtrarContenido(); 
                        } else if (typeof window.cargarEstudiantes === 'function') {
                            window.cargarEstudiantes();
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire('Error', result.value ? result.value.msg : 'Error desconocido', 'error');
                }
            }
        });
    }
});
</script>