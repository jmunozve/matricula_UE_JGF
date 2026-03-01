<?php
/**
 * modulos/views/admin/usuarios/usuarios_lista.php
 */
session_start();

// 1. Verificación de Seguridad (Autoriza Admin y Superusuario)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Convertimos el rol a minúsculas y quitamos espacios para evitar errores de escritura
$rol_actual = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : '';

// Definimos los roles que tienen permiso de entrar aquí
$roles_permitidos = ['admin', 'superusuario'];

if (!in_array($rol_actual, $roles_permitidos)) {
    header("Location: /matricula/modulos/views/login.php?error=acceso_denegado");
    exit();
}
// 2. Breadcrumbs
$breadcrumb_custom = [
    ['nombre' => 'Configuración', 'ruta' => 'admin/usuarios/configuracion.php'],
    ['nombre' => 'Gestión de Usuarios', 'ruta' => 'admin/usuarios/usuarios_lista.php']
];

// 3. Inclusión de dependencias
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/header.php';

try {
    $pdo = Conexion::abrir();
    $sql = "SELECT id as id_usuario, nombre_usuario, cedula, email, rol, estado FROM usuarios WHERE id != :id_actual ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_actual' => $_SESSION['id_usuario'] ?? 0]); 
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $usuarios = [];
}
?>

<style>
    .table-vcenter td, .table-vcenter th { vertical-align: middle; }
    .avatar-circle {
        width: 38px; height: 38px;
        background-color: #e7f1ff; color: #0d6efd;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; border-radius: 50%; font-size: 0.9rem;
    }
    .badge-role { font-size: 0.65rem; padding: 0.4em 0.8em; }
    .status-switch .form-check-input { width: 2.5em; cursor: pointer; }
    .btn-white { background: #fff; border: 1px solid #dee2e6; }
    .btn-white:hover { background: #f8f9fa; }
</style>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h4 class="fw-bold mb-0 text-uppercase">
                <i class="bi bi-shield-lock me-2 text-primary"></i>Control de Acceso
            </h4>
            <p class="text-muted small">Administración de personal y niveles de jerarquía.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <button class="btn btn-primary shadow-sm rounded-pill px-4" onclick="abrirModalUsuario()">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Usuario
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover table-vcenter mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 border-0">Usuario</th>
                        <th class="border-0">Identificación / Email</th>
                        <th class="border-0 text-center">Rol / Nivel</th>
                        <th class="border-0 text-center">Estado</th>
                        <th class="text-end pe-4 border-0">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No hay registros adicionales.</td></tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3">
                                        <?= strtoupper(substr($u['nombre_usuario'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($u['nombre_usuario']) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;">ID: #<?= $u['id_usuario'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-bold"><?= htmlspecialchars($u['cedula']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($u['email']) ?></div>
                            </td>
                            <td class="text-center">
                                <?php 
                                    $color = match($u['rol']) {
                                        'Admin'          => 'danger',
                                        'Directora'      => 'warning',
                                        'Coordinadora'   => 'dark',
                                        'Docente'        => 'info',
                                        'Administrativo' => 'primary',
                                        'Representante'  => 'success',
                                        default          => 'secondary'
                                    };
                                ?>
                                <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> text-uppercase badge-role rounded-pill border border-<?= $color ?>-subtle">
                                    <?= $u['rol'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block status-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" 
                                           <?= $u['estado'] ? 'checked' : '' ?> 
                                           onchange="toggleEstado(<?= $u['id_usuario'] ?>, this.checked)">
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                    <button class="btn btn-white btn-sm border-end" title="Editar" onclick='editarUsuario(<?= json_encode($u) ?>)'>
                                        <i class="bi bi-pencil text-primary"></i>
                                    </button>
                                    <button class="btn btn-white btn-sm" title="Restablecer Clave" onclick="resetClave(<?= $u['id_usuario'] ?>)">
                                        <i class="bi bi-key text-warning"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalTitulo">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white p-3">
                <h5 class="modal-title fw-bold" id="modalTitulo">Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUsuario">
                <input type="hidden" name="id_usuario" id="form_id_usuario">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nombre de Usuario</label>
                            <input type="text" name="nombre_usuario" id="form_nombre_usuario" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Cédula</label>
                            <input type="text" name="cedula" id="form_cedula" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" id="form_email" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Rol del Sistema</label>
                            <select name="rol" id="form_rol" class="form-select">
                                <option value="Representante">Representante</option>
                                <option value="Docente">Docente</option>
                                <option value="Coordinadora">Coordinadora</option>
                                <option value="Administrativo">Administrativo</option>
                                <option value="Directora">Directora</option>
                                <option value="Admin">Administrador</option>
                            </select>
                        </div>
                        <div class="col-12" id="contenedor_password">
                            <label class="form-label small fw-bold">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                <input type="password" name="contrasena" id="form_contrasena" class="form-control" autocomplete="new-password" placeholder="Mínimo 6 caracteres">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const modalElement = document.getElementById('modalUsuario');
    const modalInstancia = new bootstrap.Modal(modalElement);

    window.abrirModalUsuario = function() {
        // Limpiamos atributos que puedan causar el error de ARIA
        modalElement.removeAttribute('aria-hidden');
        
        document.getElementById('formUsuario').reset();
        document.getElementById('form_id_usuario').value = "";
        document.getElementById('modalTitulo').innerText = "Crear Nuevo Acceso";
        document.getElementById('contenedor_password').style.display = "block";
        document.getElementById('form_contrasena').setAttribute('required', 'required');
        modalInstancia.show();
    };

    window.editarUsuario = function(u) {
        modalElement.removeAttribute('aria-hidden');
        
        document.getElementById('formUsuario').reset();
        document.getElementById('modalTitulo').innerText = "Modificar Usuario";
        document.getElementById('form_id_usuario').value = u.id_usuario;
        document.getElementById('form_nombre_usuario').value = u.nombre_usuario;
        document.getElementById('form_cedula').value = u.cedula;
        document.getElementById('form_email').value = u.email;
        document.getElementById('form_rol').value = u.rol;
        
        document.getElementById('contenedor_password').style.display = "none";
        document.getElementById('form_contrasena').removeAttribute('required');
        modalInstancia.show();
    };

    document.getElementById('formUsuario').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const urlProcesador = "../../../controllers/usuarios/guardar_usuario.php";

        fetch(urlProcesador, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                modalInstancia.hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Logrado!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Atención', data.message, 'warning');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error crítico', 'Error de conexión con el servidor.', 'error');
        });
    };
});

function toggleEstado(id, checked) {
    const estado = checked ? 1 : 0;
    const formData = new FormData();
    formData.append('id_usuario', id);
    formData.append('nuevo_estado', estado);
    formData.append('accion', 'toggle_estado');

    fetch('../../../controllers/usuarios/guardar_usuario.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status !== 'success') {
            Swal.fire('Error', 'No se pudo actualizar el estado', 'error');
            location.reload();
        }
    });
}

function resetClave(id) {
    Swal.fire({
        title: '¿Restablecer contraseña?',
        text: "La nueva clave será el número de cédula del usuario.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        confirmButtonText: 'Sí, restablecer',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id_usuario', id);
            formData.append('accion', 'reset_password');

            fetch('../../../controllers/usuarios/guardar_usuario.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire('¡Listo!', data.message, 'success');
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}
</script>

<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/footer.php'; ?>