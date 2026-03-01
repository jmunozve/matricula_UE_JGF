/**
 * registro.js
 * Manejo de inscripciones, validaciones dinámicas y generación de Cédula Escolar.
 */

// --- 1. FUNCIONES GLOBALES ---

/**
 * Genera automáticamente el número de Cédula Escolar basándose en:
 * [Orden Gemelo] + [Últimos 2 dígitos del año de nacimiento] + [Cédula Representante normalizada a 8 dígitos]
 */
function generarEscolar() {
    const radioNo = document.getElementById('ci_no');
    const inputFecha = document.querySelector('input[name="fecha_nacimiento"]');
    const inputCiRep = document.getElementById('cedula_rep'); 
    const inputCedula = document.getElementById('input_cedula_id');
    const selectorOrden = document.getElementById('orden_gemelo'); 
    const tipoDocEst = document.querySelector('select[name="tipo_doc_es"]');

    // Solo generamos si "No tiene CI propia" está marcado
    if (!radioNo?.checked) return;

    if (inputFecha?.value && inputCiRep?.value) {
        const anio = inputFecha.value.substring(2, 4); // YY
        const orden = selectorOrden ? selectorOrden.value : '1'; 
        
        let ciLimpia = inputCiRep.value.replace(/\D/g, '');
        let ciNormalizada = ciLimpia.padStart(8, '0'); 
        
        if (inputCedula) {
            inputCedula.value = orden + anio + ciNormalizada;
        }
        if (tipoDocEst) {
            tipoDocEst.value = "CE";
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // --- REFERENCIAS AL DOM ---
    const form = document.getElementById('formRegistroCompleto');
    const btnSiguiente = document.getElementById('btnSiguienteRep');
    const btnFinalizar = document.getElementById('btnFinalizarInscripcion');

    const inputCedula = document.getElementById('input_cedula_id');
    const inputFecha = document.querySelector('input[name="fecha_nacimiento"]');
    const inputCiRep = document.getElementById('cedula_rep');
    const divDocCedula = document.getElementById('div_pdf_cedula'); 
    const selectorOrden = document.getElementById('orden_gemelo');
    const tipoDocEst = document.querySelector('select[name="tipo_doc_es"]');

    // --- MANEJO DE NAVEGACIÓN (TABS) ---
    if (btnSiguiente) {
        btnSiguiente.addEventListener('click', function() {
            // Validar solo los campos visibles de la pestaña 1 antes de pasar
            const camposPaso1 = document.getElementById('step1').querySelectorAll('input[required], select[required]');
            let valido = true;
            camposPaso1.forEach(c => { if(!c.checkValidity()) valido = false; });

            if (valido) {
                const tabEst = new bootstrap.Tab(document.getElementById('tab-est'));
                tabEst.show();
                window.scrollTo(0, 0);
            } else {
                form.reportValidity();
            }
        });
    }

    // --- MANEJO VISUAL PDF REPRESENTANTE ---
    const fileCedulaRep = document.getElementById('file_cedula_rep');
    const contenedorPdfRep = document.getElementById('contenedor-pdf-rep');
    const placeholderPdfRep = document.getElementById('preview-placeholder-rep');
    const previewPdfRep = document.getElementById('pdf-preview-rep');
    const namePdfRep = document.getElementById('pdf-name-rep');
    const btnRemovePdfRep = document.getElementById('btn-remove-file-rep');

    fileCedulaRep?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            if(contenedorPdfRep) {
                contenedorPdfRep.style.borderColor = "#198754";
                contenedorPdfRep.style.backgroundColor = "#f0fff4";
            }
            if(namePdfRep) namePdfRep.textContent = file.name;
            placeholderPdfRep?.classList.add('d-none');
            previewPdfRep?.classList.remove('d-none');
            btnRemovePdfRep?.classList.remove('d-none');
        }
    });

    btnRemovePdfRep?.addEventListener('click', function(e) {
        e.stopPropagation();
        fileCedulaRep.value = '';
        if(contenedorPdfRep) {
            contenedorPdfRep.style.borderColor = "#d1d3e2";
            contenedorPdfRep.style.backgroundColor = "";
        }
        placeholderPdfRep?.classList.remove('d-none');
        previewPdfRep?.classList.add('d-none');
        this.classList.add('d-none');
    });

    // --- LÓGICA DE ASIGNACIÓN ACADÉMICA ---
    const switchAsignar = document.getElementById('asignar_cupo');
    const contenedorAcademico = document.getElementById('contenedor_academico');
    
    if (switchAsignar) {
        switchAsignar.addEventListener('change', function() {
            const campos = contenedorAcademico.querySelectorAll('select');
            if (this.checked) {
                contenedorAcademico.style.opacity = '1';
                contenedorAcademico.style.pointerEvents = 'auto';
                campos.forEach(c => { if(c.id === 'id_modalidad') c.disabled = false; });
            } else {
                contenedorAcademico.style.opacity = '0.5';
                contenedorAcademico.style.pointerEvents = 'none';
                campos.forEach(c => { c.value = ""; if(c.id !== 'id_modalidad') c.disabled = true; });
            }
        });
    }

    // --- CARGA DE COMBOS DINÁMICOS ---
    function cargarCombo(valor, idPadre, idHijo, label, extras = []) {
        const hijo = document.getElementById(idHijo);
        if (!hijo) return;
        hijo.innerHTML = '<option value="">Cargando...</option>';
        hijo.disabled = true;

        extras.forEach(id => {
            const el = document.getElementById(id);
            if (el) { el.innerHTML = '<option value="">...</option>'; el.disabled = true; }
        });

        const esAcademico = ['niveles', 'planes', 'grados', 'secciones'].includes(valor);
        const urlBase = esAcademico ? '../../controllers/get_data.php' : '../../controllers/get_ubicacion.php';
        const param = esAcademico ? 'type' : 'tipo';

        fetch(`${urlBase}?${param}=${valor}&id=${idPadre || 0}`)
            .then(res => res.json())
            .then(data => {
                hijo.innerHTML = `<option value="">Seleccione ${label}...</option>`;
                if (data && data.length > 0) {
                    data.forEach(item => {
                        hijo.innerHTML += `<option value="${item.id}">${item.nombre}</option>`;
                    });
                    hijo.disabled = false;
                }
            })
            .catch(err => console.error("Error:", err));
    }

    // Eventos Académicos
    document.getElementById('id_modalidad')?.addEventListener('change', function() {
        cargarCombo('niveles', this.value, 'id_nivel', 'Nivel', ['id_plan', 'id_grado', 'id_seccion']);
    });
    // ... (repetir para niveles, planes, grados siguiendo la misma lógica)

    // Eventos Ubicación
    ['nac', 'hab', 'rep'].forEach(suffix => {
        document.getElementById(`id_estado_${suffix}`)?.addEventListener('change', function() {
            cargarCombo('municipios', this.value, `id_mun_${suffix}`, 'Municipio', [`id_parroquia_${suffix}`]);
        });
        document.getElementById(`id_mun_${suffix}`)?.addEventListener('change', function() {
            cargarCombo('parroquias', this.value, `id_parroquia_${suffix}`, 'Parroquia');
        });
    });

    // Carga inicial de Estados
    ['id_estado_nac', 'id_estado_hab', 'id_estado_rep'].forEach(id => cargarCombo('estados', 0, id, 'Estado'));

    // --- DIRECCIÓN SIMPLIFICADA (Copiar de Estudiante a Representante o viceversa) ---
    document.getElementById('misma_direccion')?.addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('id_estado_rep').value = document.getElementById('id_estado_hab').value;
            // Para selects dinámicos se requiere lógica adicional para clonar opciones
            document.getElementById('direccion_rep').value = document.getElementById('direccion_detalle').value;
        }
    });

    // --- GESTIÓN DE CÉDULA ESCOLAR ---
    function gestionarEstadoCedula() {
        const radioSi = document.getElementById('ci_si');
        if (radioSi?.checked) {
            inputCedula.readOnly = false;
            inputCedula.value = "";
            inputCedula.classList.remove('bg-light');
            if (selectorOrden) selectorOrden.disabled = true;
            if (divDocCedula) divDocCedula.style.display = 'block'; 
        } else {
            inputCedula.readOnly = true;
            inputCedula.classList.add('bg-light');
            if (selectorOrden) selectorOrden.disabled = false;
            if (divDocCedula) divDocCedula.style.display = 'none';
            generarEscolar();
        }
    }

    document.querySelectorAll('input[name="tiene_ci"]').forEach(r => r.addEventListener('change', gestionarEstadoCedula));
    [inputFecha, inputCiRep, selectorOrden].forEach(el => {
        el?.addEventListener('change', () => { if(document.getElementById('ci_no').checked) generarEscolar(); });
    });

    // --- FINALIZAR REGISTRO ---
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function() {
            if (form.checkValidity()) {
                Swal.fire({
                    title: '¿Confirmar registro?',
                    text: "Se guardarán los datos del estudiante y el representante",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, finalizar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        enviarFormulario();
                    }
                });
            } else {
                form.reportValidity();
            }
        });
    }

    function enviarFormulario() {
        Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        // Habilitar campos para que FormData los capture
        form.querySelectorAll(':disabled').forEach(i => i.disabled = false);

        const formData = new FormData(form);
        fetch('../../controllers/guardar_todo.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('¡Éxito!', 'Inscripción procesada', 'success').then(() => location.href='lista.php');
            } else {
                Swal.fire('Error', data.error || 'Fallo al guardar', 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Error de conexión', 'error'));
    }
});