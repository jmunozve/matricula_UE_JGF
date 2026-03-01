<?php
/**
 * F:\xampp\htdocs\matricula\config\gestion_planes.php
 * Panel de control para habilitar/deshabilitar niveles (Gestión Maestra)
 */

// 1. Verificación de Seguridad
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol_actual = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : '';

// Solo permitimos el acceso a roles de alta jerarquía
if ($rol_actual !== 'superusuario' && $rol_actual !== 'admin') { 
    header("Location: /matricula/modulos/views/dashboard.php?error=privilegios");
    exit();
}

// 2. Conexión a DB
require_once "../includes/db.php"; 

try {
    $db = Conexion::abrir();
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 3. Header y Título
$titulo_pagina = "Gestión Maestra de Planes";
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/matricula/modulos/views/dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Gestión de Inscripciones</li>
        </ol>
    </nav>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h4 class="mb-0 fw-bold text-dark">
                <i class="bi bi-shield-lock text-primary me-2"></i> Control de Inscripción por Niveles
            </h4>
        </div>
        <div class="card-body p-4">
            <p class="text-muted mb-4">
                Configure qué niveles educativos están disponibles en los formularios de registro. 
                Si cierra un nivel, no aparecerá en el registro de nuevos estudiantes.
            </p>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nivel Educativo</th>
                            <th class="text-center">Estado del Sistema</th>
                            <th class="text-end pe-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Consultamos usando la columna 'estatus' que unificamos
                        $sql = "SELECT id_nivel, nombre_nivel, estatus FROM niveles_estudio ORDER BY id_nivel ASC";
                        $stmt = $db->query($sql);
                        
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                            $esta_activo = (trim($row['estatus']) === 'Activo');
                        ?>
                        <tr>
                            <td class="ps-3">
                                <span class="fw-bold text-secondary"><?= htmlspecialchars($row['nombre_nivel']) ?></span>
                            </td>
                            <td class="text-center">
                                <?php if($esta_activo): ?>
                                    <span class="badge rounded-pill bg-success-subtle text-success px-3 border border-success-subtle">
                                        <i class="bi bi-check-circle-fill me-1"></i> ABIERTO
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-danger-subtle text-danger px-3 border border-danger-subtle">
                                        <i class="bi bi-x-circle-fill me-1"></i> CERRADO
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3">
                                <button onclick="confirmarCambio(<?= $row['id_nivel'] ?>, '<?= $row['estatus'] ?>', '<?= $row['nombre_nivel'] ?>')" 
                                        class="btn btn-sm <?= $esta_activo ? 'btn-outline-danger' : 'btn-success' ?> rounded-pill px-3 fw-bold shadow-sm">
                                    <?= $esta_activo ? '<i class="bi bi-lock me-1"></i> Cerrar' : '<i class="bi bi-unlock me-1"></i> Abrir' ?>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <a href="/matricula/modulos/views/dashboard.php" class="btn btn-link text-decoration-none text-muted">
            <i class="bi bi-arrow-left"></i> Volver al panel principal
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmarCambio(id, estatusActual, nombreNivel) {
    const esActivo = (estatusActual === 'Activo');
    const titulo = esActivo ? '¿Cerrar Inscripción?' : '¿Abrir Inscripción?';
    const texto = esActivo 
        ? `El nivel "${nombreNivel}" dejará de ser visible en los formularios.` 
        : `El nivel "${nombreNivel}" volverá a estar disponible para registrar estudiantes.`;
    
    Swal.fire({
        title: titulo,
        text: texto,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: esActivo ? '#dc3545' : '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: esActivo ? 'Sí, cerrar nivel' : 'Sí, abrir nivel',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviamos el nuevo estado como 0 o 1 para que el controlador lo procese fácilmente
            const nuevoEstado = esActivo ? 0 : 1;
            window.location.href = "procesar_estatus.php?id=" + id + "&estado=" + nuevoEstado;
        }
    });
}
</script>

<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/footer.php'; 
?>