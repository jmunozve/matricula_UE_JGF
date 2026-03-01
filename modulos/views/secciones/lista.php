<?php

/**
 * modulos/views/estudiantes/lista.php
 * Vista principal que orquesta las tablas y listas vía AJAX
 */
include_once "../../../includes/header.php";
?>

<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-dark">Gestión de Estudiantes</h4>
            <p class="text-muted small mb-0">Administra la matrícula, expedientes y distribución de secciones</p>
        </div>

        <div class="d-flex gap-2 align-items-center">
            <div class="d-flex gap-2 me-3 border-end pe-3">
                <a href="../secciones/crear_seccion.php" class="btn btn-outline-primary shadow-sm fw-bold px-3 btn-hover-custom">
                    <i class="bi bi-layer-forward me-1"></i> NUEVA SECCIÓN
                </a>

                <a href="../representante/registro.php" class="btn btn-success shadow-sm fw-bold px-3 btn-hover-custom">
                    <i class="bi bi-person-plus-fill me-1"></i> NUEVO ESTUDIANTE
                </a>
            </div>

            <div class="btn-group shadow-sm">
                <button type="button" id="btn-tabla" class="btn btn-primary d-flex align-items-center" onclick="cambiarVista('tabla', this)">
                    <i class="bi bi-table me-2"></i> Tabla
                </button>
                <button type="button" id="btn-list" class="btn btn-white border d-flex align-items-center" onclick="cambiarVista('list', this)">
                    <i class="bi bi-grid-3x3-gap me-2"></i> Mosaico
                </button>
                <button type="button" id="btn-secciones" class="btn btn-white border d-flex align-items-center" onclick="cambiarVista('secciones', this)">
                    <i class="bi bi-diagram-3 me-2"></i> Secciones
                </button>
            </div>
        </div>
    </div>

    <div id="contenedor-dinamico" class="card shadow-sm border-0 overflow-hidden" style="min-height: 600px; border-radius: 12px;">
        <div class="d-flex flex-column justify-content-center align-items-center" style="height: 600px; background: #fdfdfd;">
            <div class="spinner-grow text-primary mb-3" role="status"></div>
            <span class="text-muted fw-bold">Cargando información...</span>
        </div>
    </div>
</div>

<style>
    /* Efectos para los botones principales */
    .btn-hover-custom {
        transition: all 0.2s ease;
        border-radius: 8px;
    }

    .btn-hover-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    /* Estilos corregidos para el grupo de botones */
    .btn-white {
        background-color: #ffffff !important;
        color: #333 !important;
        /* Texto oscuro para visibilidad */
        border-color: #dee2e6 !important;
    }

    .btn-white:hover {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }

    /* Aseguramos que el botón primario siempre tenga texto blanco */
    .btn-group .btn-primary {
        color: #ffffff !important;
    }

    /* Aseguramos que los iconos sigan el color del texto */
    .btn-group .btn i {
        color: inherit;
    }
</style>

<?php
include_once "../../../includes/modal_detalle_seccion.php";
include_once "../../../includes/modales_estudiante.php";
include_once "../../../includes/footer.php";
include_once "../../../includes/modal_retiro.php";
?>

<script>
    const RUTA_RAIZ = "../../../";
    let vistaActiva = 'tabla';

    /**
     * NAVEGACIÓN Y EFECTOS DE BOTONES
     */
    function cambiarVista(vista, btn) {
        vistaActiva = vista;

        // 1. Resetear todos los botones a estado "Inactivo"
        document.querySelectorAll('.btn-group .btn').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-white', 'border');
            b.style.color = "#333"; // Forzar color oscuro
        });

        // 2. Activar el botón seleccionado
        if (btn) {
            btn.classList.remove('btn-white', 'border');
            btn.classList.add('btn-primary');
            btn.style.color = "#fff"; // Forzar color blanco
        }

        filtrarContenido();
    }

    /**
     * MOTOR DE FILTRADO UNIFICADO
     */
    const ejecutarFiltros = () => {
        const buscador = document.getElementById('inputBusquedaGlobal');
        const term = buscador ? buscador.value.trim().toLowerCase() : "";
        const status = document.getElementById('statusBusqueda');

        if (vistaActiva === 'list') {
            const selNivel = document.getElementById('filtro-nivel-list');
            if (!selNivel) return;

            const nivelVal = selNivel.value.trim().toLowerCase();
            const msgIni = document.getElementById('msg-inicial');
            const tarjetas = document.querySelectorAll('.item-estudiante');

            if (msgIni) msgIni.style.display = (nivelVal === "") ? 'block' : 'none';
            if (buscador) buscador.disabled = (nivelVal === "");

            let encontrados = 0;
            tarjetas.forEach(t => {
                const nCard = (t.getAttribute('data-nivel') || "").toLowerCase();
                const sCard = (t.getAttribute('data-search') || "").toLowerCase();

                const cumpleNivel = (nCard === nivelVal);
                const cumpleBusca = (term === "" || sCard.includes(term));

                if (nivelVal !== "" && cumpleNivel && cumpleBusca) {
                    t.style.setProperty('display', 'block', 'important');
                    encontrados++;
                } else {
                    t.style.setProperty('display', 'none', 'important');
                }
            });

            if (status) {
                status.innerHTML = nivelVal === "" ? "Seleccione un nivel" : `<i class="bi bi-people"></i> ${encontrados} Alumnos`;
            }
        } else {
            const elementos = document.querySelectorAll('.fila-datos, .card-seccion');
            let encontrados = 0;

            elementos.forEach(el => {
                const texto = el.innerText.toLowerCase();
                if (term === "" || texto.includes(term)) {
                    el.style.display = "";
                    encontrados++;
                } else {
                    el.style.display = "none";
                }
            });

            if (status) {
                status.style.display = (term === "") ? "none" : "inline-block";
                status.innerHTML = `Resultados: ${encontrados}`;
            }
        }
    };

    /**
     * CARGA DE CONTENIDO AJAX
     */
    function filtrarContenido(pagina = 1) {
        const contenedor = document.getElementById('contenedor-dinamico');
        if (!contenedor) return;

        contenedor.style.opacity = '0.5';
        let url = (vistaActiva === 'secciones') ?
            `lista_secciones.php` :
            `../estudiante/vistas_estudiantes/get_${vistaActiva}.php?p=${pagina}`;

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error(`No se encontró: ${url}`);
                return res.text();
            })
            .then(html => {
                contenedor.innerHTML = html;
                contenedor.style.opacity = '1';
                setTimeout(ejecutarFiltros, 50);
            })
            .catch(err => {
                console.error("Error de carga:", err);
                contenedor.innerHTML = `<div class='alert alert-danger m-3'>Error al cargar la vista.</div>`;
                contenedor.style.opacity = '1';
            });
    }

    /**
     * EVENTOS DE ESCUCHA
     */
    document.addEventListener('change', (e) => {
        if (e.target.id === 'filtro-nivel-list') ejecutarFiltros();
    });

    document.addEventListener('input', (e) => {
        if (e.target.id === 'inputBusquedaGlobal') ejecutarFiltros();
    });

    // Carga inicial
    document.addEventListener("DOMContentLoaded", () => {
        filtrarContenido();
    });

    /**
     * MODALES Y ACCIONES (EDICIÓN, ELIMINACIÓN, ETC)
     */
    window.verDetalleSeccion = function(idSeccion) {
        const modalEl = document.getElementById('modalDetalleSeccion');
        const contenedor = document.getElementById('datosSeccion');
        const spinner = document.getElementById('spinnerCarga');
        if (!modalEl || !contenedor) return;

        let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        if (spinner) spinner.classList.remove('d-none');
        contenedor.innerHTML = "";
        modal.show();

        fetch(`../../controllers/secciones/obtener_detalle_seccion.php?id=${idSeccion}`)
            .then(res => res.text())
            .then(html => {
                if (spinner) spinner.classList.add('d-none');
                contenedor.innerHTML = html;
            })
            .catch(err => {
                if (spinner) spinner.classList.add('d-none');
                contenedor.innerHTML = `<div class="alert alert-danger">Error al cargar datos.</div>`;
            });
    };

    window.abrirEdicion = (id) => {
        if (typeof abrirModalEditar === 'function') {
            abrirModalEditar(id, RUTA_RAIZ);
        } else {
            Swal.fire("Error", "No se cargó la lógica de edición", "error");
        }
    };

    window.verEstudiantes = (id) => window.verDetalleSeccion(id);

    function eliminarSeccion(id) {
        // Usamos SweetAlert2 si lo tienes, o confirm normal
        if (!confirm('¿Estás seguro de eliminar esta sección?')) return;

        // Ajustamos la ruta al nombre exacto de tu archivo: borrar_seccion.php
        fetch(`../../controllers/secciones/borrar_seccion.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    // Si usas tu función de filtrado para refrescar la tabla sin recargar toda la página
                    if (typeof filtrarContenido === 'function') {
                        filtrarContenido();
                    } else {
                        location.reload();
                    }
                } else if (data.status === 'warning') {
                    alert('Atención: ' + data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error en la comunicación con el servidor');
            });
    }
</script>