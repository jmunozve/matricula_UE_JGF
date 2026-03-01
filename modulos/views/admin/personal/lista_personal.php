<?php
$root = $_SERVER['DOCUMENT_ROOT'] . '/matricula';
require_once $root . '/includes/db.php';
require_once $root . '/includes/header.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Seguridad
$rol_actual = strtolower(trim($_SESSION['rol'] ?? ''));
$permitidos = ['superusuario', 'director', 'directora', 'coordinador', 'coordinadora'];

if (!in_array($rol_actual, $permitidos)) {
    echo "<script>window.location.href='/matricula/modulos/views/dashboard.php?error=acceso_denegado';</script>";
    exit();
}

try {
    $pdo = Conexion::abrir();
    
    // CONSULTA ACTUALIZADA: Usamos JOIN para traer el nombre de la especialidad
    $sql = "SELECT p.*, e.nombre_especialidad 
            FROM personal p 
            LEFT JOIN especialidades e ON p.id_especialidad = e.id_especialidad 
            ORDER BY p.cargo ASC, p.apellido ASC";
    
    $stmt = $pdo->query($sql);
    $lista_personal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Traemos las especialidades para el select del modal (organizadas por tipo)
    $stmtEsp = $pdo->query("SELECT * FROM especialidades ORDER BY tipo_personal ASC, nombre_especialidad ASC");
    $especialidades = $stmtEsp->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error al cargar datos: " . $e->getMessage());
}
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 mb-4 bg-purple text-white" style="background: linear-gradient(45deg, #6f42c1, #8e24aa);">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-people-fill me-2"></i>Gestión de Personal</h3>
                <p class="mb-0 opacity-75">Panel unificado: Directivos, Docentes, Administrativos y Obreros.</p>
            </div>
            <button class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" onclick="nuevoPersonal()">
                <i class="bi bi-person-plus-fill me-2"></i>Registrar Personal
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th>Cédula</th>
                            <th>Nombre Completo</th>
                            <th>Cargo</th>
                            <th>Especialidad / Área</th>
                            <th class="text-center">Estatus</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lista_personal as $p): 
                            // Colores para cargos
                            $colorCargo = 'bg-secondary';
                            if($p['cargo'] == 'Directivo') $colorCargo = 'bg-dark';
                            if($p['cargo'] == 'Docente') $colorCargo = 'bg-primary';
                            if($p['cargo'] == 'Administrativo') $colorCargo = 'bg-info text-dark';
                            if($p['cargo'] == 'Obrero') $colorCargo = 'bg-warning text-dark';
                            
                            $estatusClase = ($p['estatus'] == 'Activo') ? 'bg-success' : 'bg-danger';
                        ?>
                        <tr>
                            <td><strong><?= $p['cedula'] ?></strong></td>
                            <td><?= mb_strtoupper($p['apellido'] . ", " . $p['nombre']) ?></td>
                            <td><span class="badge <?= $colorCargo ?>"><?= $p['cargo'] ?></span></td>
                            <td><?= $p['nombre_especialidad'] ?: '<span class="text-muted small">Sin definir</span>' ?></td>
                            <td class="text-center">
                                <span class="badge rounded-pill <?= $estatusClase ?> px-3"><?= $p['estatus'] ?></span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group shadow-sm">
                                    <button class="btn btn-sm btn-outline-primary" onclick='editarPersonal(<?= json_encode($p) ?>)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="cambiarEstatus(<?= $p['id_docente'] ?>, '<?= $p['estatus'] ?>')">
                                        <i class="bi bi-power"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once $root . '/modulos/views/admin/personal/modal_personal.php'; ?>

<script src="/matricula/public/assets/js/jquery.min.js"></script>
<script src="/matricula/public/assets/js/sweetalert2.all.min.js"></script>

<script>
function nuevoPersonal() {
    $('#formPersonal')[0].reset();
    $('#id_docente').val('');
    $('#modalTitle').html('<i class="bi bi-person-plus-fill me-2"></i>Registrar Personal');
    $('#modalPersonal').modal('show');
}

function editarPersonal(p) {
    $('#id_docente').val(p.id_docente);
    $('#cedula').val(p.cedula);
    $('#nombre').val(p.nombre);
    $('#apellido').val(p.apellido);
    $('#cargo').val(p.cargo);
    // IMPORTANTE: Ahora asignamos el ID de la especialidad al select
    $('#id_especialidad').val(p.id_especialidad); 
    $('#telefono').val(p.telefono);
    $('#email').val(p.email);
    $('#modalTitle').html('<i class="bi bi-pencil-square me-2"></i>Editar Personal');
    $('#modalPersonal').modal('show');
}

$("#formPersonal").on("submit", function(e) {
    e.preventDefault();
    $.ajax({
        url: '/matricula/modulos/controllers/admin/personal/guardar_personal.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            // Asumiendo que el controlador devuelve un JSON
            try {
                const response = typeof res === 'string' ? JSON.parse(res) : res;
                if(response.status === 'success') {
                    location.reload();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            } catch (e) {
                location.reload(); // Recarga si el controlador no devuelve JSON pero guardó bien
            }
        }
    });
});

function cambiarEstatus(id, estatusActual) {
    const nuevo = (estatusActual === 'Activo') ? 'Inactivo' : 'Activo';
    Swal.fire({
        title: '¿Cambiar estatus?',
        text: `El personal pasará a estar ${nuevo}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/matricula/modulos/controllers/admin/personal/cambiar_estatus.php', {id: id, estatus: nuevo}, function(res) {
                location.reload();
            });
        }
    });
}
</script>

<?php require_once $root . '/includes/footer.php'; ?>