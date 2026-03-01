<style>
    /* Control de altura y centrado de la modal */
    #modalEditarEstudiante .modal-dialog {
        max-height: 95vh;
        display: flex;
        align-items: center;
    }

    #modalEditarEstudiante .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        border-radius: 1rem;
        overflow: hidden;
    }

    /* Contenedor con scroll interno estético */
    #contenidoModalEditar {
        overflow-y: auto !important;
        flex: 1;
        scrollbar-width: thin;
        scrollbar-color: #0d6efd #f8f9fc;
    }

    /* Scrollbar para navegadores Webkit (Chrome, Safari, Edge) */
    #contenidoModalEditar::-webkit-scrollbar { width: 8px; }
    #contenidoModalEditar::-webkit-scrollbar-track { background: #f8f9fc; }
    #contenidoModalEditar::-webkit-scrollbar-thumb {
        background-color: #0d6efd;
        border-radius: 10px;
        border: 2px solid #f8f9fc;
    }

    .loading-state {
        min-height: 400px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
</style>

<div class="modal fade" id="modalEditarEstudiante" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalEditarLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div id="contenidoModalEditar" class="container-fluid p-0"></div>
        </div>
    </div>
</div>

<script>
window.abrirModalEditar = function(id, ruta) {
    const modalEl = document.getElementById('modalEditarEstudiante');
    const contenedor = $("#contenidoModalEditar");
    
    // Limpieza agresiva inicial
    modalEl.removeAttribute('aria-hidden');
    
    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    
    contenedor.html(`
        <div class="loading-state p-5 text-center">
            <div class="spinner-border text-primary" style="width: 3.5rem; height: 3.5rem;" role="status"></div>
            <h4 class="mt-4 fw-bold text-dark">Sincronizando Expediente</h4>
            <p class="text-muted">Accediendo a la base de datos...</p>
        </div>
    `);
    
    bsModal.show();
    
    const base = ruta.endsWith('/') ? ruta : ruta + '/';
    const urlDestino = `${base}modulos/views/estudiante/editar_estudiante.php?id=${id}`;
    
    $.ajax({
        url: urlDestino,
        method: 'GET',
        cache: false,
        success: function(response) {
            contenedor.hide().html(response).fadeIn(250);
            
            // Forzar el foco sin que el navegador se queje
            setTimeout(() => {
                const closeBtn = modalEl.querySelector('.btn-close');
                if (closeBtn) closeBtn.focus({ preventScroll: true });
            }, 300);
        },
        error: function(xhr) {
            contenedor.html(`
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title">ERROR DE CARGA</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-5">
                    <i class="bi bi-cloud-slash text-danger" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 fw-bold">No se pudo recuperar el expediente</h5>
                    <button class="btn btn-secondary px-4 mt-2" data-bs-dismiss="modal">Cerrar</button>
                </div>
            `);
        }
    });
};

/**
 * FIX RADICAL PARA EL ERROR DE CONSOLA
 * Vigila la modal y elimina aria-hidden en cuanto Bootstrap intenta ponerlo
 */
const fixModalAccesibilidad = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
            const el = mutation.target;
            if (el.getAttribute('aria-hidden') === 'true' && el.classList.contains('show')) {
                el.removeAttribute('aria-hidden');
            }
        }
    });
});

const modalTarget = document.getElementById('modalEditarEstudiante');
if (modalTarget) {
    fixModalAccesibilidad.observe(modalTarget, { attributes: true });
}

// Limpieza al cerrar
modalTarget.addEventListener('hidden.bs.modal', function () {
    this.removeAttribute('aria-hidden');
});
</script>