<style>
    /* Estilos para controlar el scroll y dimensiones */
    #modalEditarRepresentante .modal-dialog {
        max-height: 95vh; 
        display: flex;
        align-items: center; 
    }

    #modalEditarRepresentante .modal-content {
        height: 90vh; /* Altura fija para que el flex interno funcione */
        display: flex;
        flex-direction: column;
        border-radius: 1rem;
        overflow: hidden;
    }

    /* Contenedor donde se inyecta el HTML */
    #contenidoModalEditarRep {
        overflow-y: auto !important;
        flex: 1; /* Ocupa todo el espacio disponible */
        scrollbar-width: thin;
        scrollbar-color: #198754 #f8f9fc;
    }

    /* Scrollbar Webkit (Chrome/Edge/Safari) */
    #contenidoModalEditarRep::-webkit-scrollbar { width: 8px; }
    #contenidoModalEditarRep::-webkit-scrollbar-track { background: #f8f9fc; }
    #contenidoModalEditarRep::-webkit-scrollbar-thumb {
        background-color: #198754;
        border-radius: 10px;
        border: 2px solid #f8f9fc;
    }
</style>

<div class="modal fade" id="modalEditarRepresentante" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div id="contenidoModalEditarRep" class="container-fluid p-0">
                </div>
        </div>
    </div>
</div>

<script>
function abrirModalEditarRep(id, rutaBase = "") {
    if (window.event) {
        window.event.preventDefault(); 
        window.event.stopPropagation();
    }

    const modalElement = document.getElementById('modalEditarRepresentante');
    const contenedor = $("#contenidoModalEditarRep");

    // Limpieza de aria-hidden antes de mostrar
    $(modalElement).removeAttr('aria-hidden');

    let myModal = bootstrap.Modal.getInstance(modalElement);
    if (!myModal) {
        myModal = new bootstrap.Modal(modalElement);
    }
    
    myModal.show();
    
    // Loader centrado
    contenedor.html(`
        <div class="d-flex flex-column align-items-center justify-content-center" style="height: 100%;">
            <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status"></div>
            <h4 class="mt-4 fw-bold">Sincronizando Expediente</h4>
            <p class="text-muted small">Cargando base de datos local...</p>
        </div>
    `);
    
    const separator = (rutaBase && !rutaBase.endsWith('/')) ? '/' : '';
    const urlDestino = `${rutaBase}${separator}modulos/views/representante/editar_representante.php?id=${id}`;
    
    $.ajax({
        url: urlDestino,
        method: 'GET',
        cache: false,
        success: function(response) {
            contenedor.html(response);
            // Reforzamos la eliminación después de la carga
            $(modalElement).removeAttr('aria-hidden');
        },
        error: function(xhr) {
            contenedor.html(`
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title">Error de Sistema</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-5">
                    <i class="bi bi-exclamation-triangle text-danger display-1"></i>
                    <p class="mt-3 fw-bold">No se pudo recuperar el archivo:</p>
                    <code class="d-block p-2 bg-light border mb-3">${urlDestino}</code>
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar Ventana</button>
                </div>
            `);
        }
    });
}
</script>