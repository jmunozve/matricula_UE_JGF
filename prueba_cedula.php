<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Simulador de Base de Datos Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .case-card { border-left: 5px solid #0d6efd; transition: 0.3s; }
        .case-card:hover { background-color: #f8f9fa; }
    </style>
</head>
<body class="bg-light p-4">

<div class="container">
    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm p-4 mb-4">
                <h4 class="text-primary">Generador de Casos</h4>
                <hr>
                
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="morocho_switch" onchange="toggleMorocho()">
                    <label class="form-check-label" for="morocho_switch">¿Es Morocho / Parto Múltiple?</label>
                </div>

                <div id="div_orden" class="mb-3" style="display: none;">
                    <label class="form-label">Orden del hijo (Prefijo):</label>
                    <input type="number" id="orden_hijo" class="form-control" value="1" min="1" oninput="generarCE()">
                </div>

                <div class="mb-3">
                    <label class="form-label">Cédula Representante:</label>
                    <input type="number" id="cedula_rep" class="form-control" placeholder="Ej: 5698785" oninput="generarCE()">
                </div>

                <div class="mb-3">
                    <label class="form-label">Fecha Nac. Estudiante:</label>
                    <input type="date" id="fecha_nac" class="form-control" onchange="generarCE()">
                </div>

                <div class="p-3 bg-light rounded mb-3 border">
                    <small class="text-muted d-block">Cédula Escolar Generada:</small>
                    <strong id="resultado_ce" class="h3 text-dark">---</strong>
                </div>

                <button class="btn btn-primary w-100" onclick="guardarEnArray()">Simular Guardado en BD</button>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm p-4">
                <h4 class="text-secondary">Simulación de Tabla: <code>estudiantes</code></h4>
                <p class="small text-muted">Aquí se van acumulando los registros guardados en el array.</p>
                
                <table class="table table-hover mt-2">
                    <thead class="table-dark">
                        <tr>
                            <th>ID (BD)</th>
                            <th>Cédula Escolar</th>
                            <th>Morocho</th>
                            <th>Año</th>
                            <th>CI Rep (Formateada)</th>
                        </tr>
                    </thead>
                    <tbody id="tabla_bd">
                        </tbody>
                </table>
                <div id="vacio_msg" class="text-center py-4 text-muted">No hay registros en la "Base de Datos".</div>
            </div>
        </div>
    </div>
</div>

<script>
// ESTA ES TU BASE DE DATOS SIMULADA (ARRAY)
let db_estudiantes = [];
let contador_id = 1;

function toggleMorocho() {
    const isChecked = document.getElementById('morocho_switch').checked;
    document.getElementById('div_orden').style.display = isChecked ? 'block' : 'none';
    generarCE();
}

function generarCE() {
    const cedula = document.getElementById('cedula_rep').value;
    const fecha = document.getElementById('fecha_nac').value;
    const isMorocho = document.getElementById('morocho_switch').checked;
    const orden = document.getElementById('orden_hijo').value;
    const display = document.getElementById('resultado_ce');

    if (cedula && fecha) {
        const prefijo = isMorocho ? orden : "1";
        const anio = fecha.substring(2, 4);
        const cedulaPadded = cedula.toString().padStart(8, '0');
        display.innerText = prefijo + anio + cedulaPadded;
        return { ce: prefijo + anio + cedulaPadded, isMorocho, anio, ci_formateada: cedulaPadded };
    }
    display.innerText = "---";
    return null;
}

function guardarEnArray() {
    const datos = generarCE();
    if (!datos) {
        alert("Completa los datos primero");
        return;
    }

    // Guardamos el objeto en el array (Como un INSERT en SQL)
    const nuevoRegistro = {
        id: contador_id++,
        ce: datos.ce,
        morocho: datos.isMorocho ? "SÍ (Orden " + document.getElementById('orden_hijo').value + ")" : "NO",
        anio: datos.anio,
        ci_rep: datos.ci_formateada
    };

    db_estudiantes.push(nuevoRegistro);
    actualizarTablaUI();
}

function actualizarTablaUI() {
    const tabla = document.getElementById('tabla_bd');
    const msg = document.getElementById('vacio_msg');
    
    msg.style.display = "none";
    tabla.innerHTML = ""; // Limpiar tabla

    db_estudiantes.forEach(reg => {
        tabla.innerHTML += `
            <tr class="case-card">
                <td>${reg.id}</td>
                <td class="fw-bold text-primary">${reg.ce}</td>
                <td>${reg.morocho}</td>
                <td>${reg.anio}</td>
                <td>${reg.ci_rep}</td>
            </tr>
        `;
    });
}
</script>

</body>
</html>