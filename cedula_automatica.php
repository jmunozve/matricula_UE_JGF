<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Matrícula - Modal de Validación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .morocho-row { background-color: #fff3cd !important; border-left: 5px solid #ffc107; }
        .modal-header-warning { background-color: #ffc107; color: #000; }
    </style>
</head>
<body class="bg-light p-4">

<div class="container-fluid">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow border-0 p-4">
                <h5 class="text-primary border-bottom pb-2">Inscripción de Estudiante</h5>
                
                <div class="mb-3 mt-2">
                    <label class="form-label fw-bold">Nombre Completo</label>
                    <input type="text" id="nombre_est" class="form-control" placeholder="Ej: Luis Garcia">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Cédula Representante</label>
                    <input type="number" id="cedula_rep" class="form-control" oninput="procesarLogica()">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Fecha de Nacimiento</label>
                    <input type="date" id="fecha_nac" class="form-control" onchange="procesarLogica()">
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label fw-bold">¿Posee Cédula?</label>
                    <select id="tiene_cedula" class="form-select" onchange="toggleCedulaFields()">
                        <option value="NO">No (Generar CE)</option>
                        <option value="SI">Sí (C.I. Real)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Número de Identificación</label>
                    <input type="text" id="cedula_input" class="form-control bg-light" readonly>
                </div>

                <button class="btn btn-primary w-100 fw-bold py-2" onclick="validarConModal()">GUARDAR REGISTRO</button>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow border-0 p-4">
                <h5 class="text-secondary border-bottom pb-2">Base de Datos Simulada</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Nombre</th>
                                <th>Cédula</th>
                                <th>C. Escolar</th>
                                <th>Rep.</th>
                                <th>Fecha Nac.</th>
                                <th>Parto Mult.</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_bd"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let db_estudiantes = [];
let modalInstancia = new bootstrap.Modal(document.getElementById('morochoModal'));

function toggleCedulaFields() {
    const tiene = document.getElementById('tiene_cedula').value;
    const input = document.getElementById('cedula_input');
    if (tiene === 'SI') {
        input.readOnly = false;
        input.classList.remove('bg-light');
        input.value = "";
    } else {
        input.readOnly = true;
        input.classList.add('bg-light');
        procesarLogica();
    }
}




function procesarLogica() {
    const tiene = document.getElementById('tiene_cedula').value;
    const ciRep = document.getElementById('cedula_rep').value;
    const fecha = document.getElementById('fecha_nac').value;

    if (tiene === 'NO' && ciRep && fecha) {
        const ciPadded = ciRep.toString().padStart(8, '0');
        const anio = fecha.substring(2, 4);
        const coincidencias = db_estudiantes.filter(e => e.cedula_rep === ciPadded && e.fecha_nac === fecha).length;
        const prefijo = (coincidencias + 1).toString();
        document.getElementById('cedula_input').value = prefijo + anio + ciPadded;
    }
}

function validarConModal() {
    const nombre = document.getElementById('nombre_est').value;
    const ciRep = document.getElementById('cedula_rep').value.toString().padStart(8, '0');
    const fecha = document.getElementById('fecha_nac').value;

    if (!nombre || !ciRep || !fecha) return alert("Complete todos los campos.");

    const hermanos = db_estudiantes.filter(e => e.cedula_rep === ciRep && e.fecha_nac === fecha);

    if (hermanos.length > 0) {
        // Llenar datos en la modal
        document.getElementById('modal_rep').innerText = ciRep;
        document.getElementById('modal_nuevo_nombre').innerText = nombre;
        const lista = document.getElementById('lista_hermanos');
        lista.innerHTML = "";
        hermanos.forEach(h => {
            let li = document.createElement('li');
            li.innerText = h.nombre;
            lista.appendChild(li);
        });
        
        modalInstancia.show();
    } else {
        ejecutarInsercion(nombre, ciRep, fecha, 0);
    }
}

function confirmarParto(valor) {
    const nombre = document.getElementById('nombre_est').value;
    const ciRep = document.getElementById('cedula_rep').value.toString().padStart(8, '0');
    const fecha = document.getElementById('fecha_nac').value;

    if (valor === 1) {
        // Actualizar retroactivamente a los hermanos encontrados
        db_estudiantes.forEach(e => {
            if (e.cedula_rep === ciRep && e.fecha_nac === fecha) {
                e.parto_multiple = 1;
            }
        });
    }
    
    modalInstancia.hide();
    ejecutarInsercion(nombre, ciRep, fecha, valor);
}

function ejecutarInsercion(nombre, ciRep, fecha, valorParto) {
    const tiene = document.getElementById('tiene_cedula').value;
    const idValor = document.getElementById('cedula_input').value;

    db_estudiantes.push({
        nombre: nombre,
        cedula: idValor,
        cedula_escolar: (tiene === 'NO') ? idValor : "",
        cedula_rep: ciRep,
        fecha_nac: fecha,
        parto_multiple: valorParto
    });

    actualizarTabla();
    limpiarFormulario();
}

function limpiarFormulario() {
    document.getElementById('nombre_est').value = "";
    document.getElementById('cedula_input').value = "";
    if(document.getElementById('tiene_cedula').value === 'NO') procesarLogica();
}

function actualizarTabla() {
    const tabla = document.getElementById('tabla_bd');
    tabla.innerHTML = "";
    db_estudiantes.forEach(e => {
        tabla.innerHTML += `
            <tr class="${e.parto_multiple == 1 ? 'morocho-row' : ''}">
                <td class="text-start ps-3">${e.nombre}</td>
                <td class="fw-bold">${e.cedula}</td>
                <td class="text-primary">${e.cedula_escolar || '-'}</td>
                <td>${e.cedula_rep}</td>
                <td>${e.fecha_nac}</td>
                <td class="fw-bold text-danger">${e.parto_multiple}</td>
            </tr>
        `;
    });
}
</script>
</body>
</html>