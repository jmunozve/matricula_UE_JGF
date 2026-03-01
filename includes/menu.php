<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$ruta = $_SESSION['ruta_activa'] ?? 'dashboard';
$usuario = $_SESSION['usuario'] ?? ['rol' => 'usuario'];
$rol = strtolower(trim($usuario['rol'] ?? 'usuario'));
?>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar">
  <!-- Encabezado -->
  <div class="sidebar-header text-center mb-3">
    <img src="/matricula/public/assets/img/logo.png" alt="Logo" width="40" class="sidebar-logo">
    <h6 class="sidebar-title mb-0 fw-bold">Panel</h6>
  </div>

  <!-- Botón cierre (móvil) -->
  <button class="sidebar-close btn btn-sm btn-outline-secondary mb-3 d-md-none" onclick="toggleSidebarMobile()">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
      <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
    </svg>
    <span class="ms-1">Cerrar</span>
  </button>

  <!-- Menú -->
  <ul class="menu-items">

    <!-- Dashboard (todos los roles) -->
    <li class="menu-item">
      <a href="/matricula/router.php?ruta=dashboard" class="nav-link <?= $ruta === 'dashboard' ? 'active' : '' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#00ffd0" viewBox="0 0 16 16" class="menu-icon">
          <path d="M0 0h6v6H0V0zm10 0h6v6h-6V0zM0 10h6v6H0v-6zm10 10h6v-6h-6v6z"/>
        </svg>
        <span class="nav-text">Dashboard</span>
      </a>
    </li>

    <!-- Institución (todos los roles) -->
    <li class="menu-item">
      <a href="/matricula/router.php?ruta=institucion/index" class="nav-link <?= $ruta === 'institucion/index' ? 'active' : '' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#00ffd0" viewBox="0 0 16 16" class="menu-icon">
          <path d="M8 0L0 4v2h16V4L8 0zM0 8v8h4V8H0zm6 0v8h4V8H6zm6 0v8h4V8h-4z"/>
        </svg>
        <span class="nav-text">Institución</span>
      </a>
    </li>

    <!-- Estudiantes (solo admin y docente) -->
    <?php if (in_array($rol, ['admin', 'usuario'])): ?>
    <li class="menu-item">
      <a href="/matricula/router.php?ruta=estudiantes" class="nav-link <?= $ruta === 'estudiantes' ? 'active' : '' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#00ffd0" viewBox="0 0 16 16" class="menu-icon">
          <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0-3-3 3 3 0 0 0 3 3z"/>
        </svg>
        <span class="nav-text">Estudiantes</span>
      </a>
    </li>
    <?php endif; ?>

    <!-- Académico (solo admin) -->
    <?php if (in_array($rol, ['admin'])): ?>
    <li class="menu-item">
      <a href="/matricula/router.php?ruta=academico" class="nav-link <?= $ruta === 'academico' ? 'active' : '' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#00ffd0" viewBox="0 0 16 16" class="menu-icon">
          <path d="M8 0L0 4l8 4 8-4-8-4zm0 5.5L3.5 4 8 2.5 12.5 4 8 5.5zM0 6v6c0 1 2 2 8 2s8-1 8-2V6l-8 4-8-4z"/>
        </svg>
        <span class="nav-text">Académico</span>
      </a>
    </li>
    <?php endif; ?>

    <!-- configuración  (solo admin) -->
    <?php if (in_array($rol, ['admin'])): ?>
    <li class="menu-item">
      <a href="/matricula/router.php?ruta=configuracion" class="nav-link <?= $ruta === 'configuracion' ? 'active' : '' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#00ffd0" viewBox="0 0 16 16" class="menu-icon">
          <path d="M8 0L0 4l8 4 8-4-8-4zm0 5.5L3.5 4 8 2.5 12.5 4 8 5.5zM0 6v6c0 1 2 2 8 2s8-1 8-2V6l-8 4-8-4z"/>
        </svg>
        <span class="nav-text">Configuración</span>
      </a>
    </li>
    <?php endif; ?>


  </ul>
</aside>

<!-- Overlay móvil -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>

<!-- Scripts -->
<script>
  function toggleSidebarMobile() {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebar-overlay').classList.toggle('show');
  }

  window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) {
      document.getElementById('sidebar').classList.remove('show');
      document.getElementById('sidebar-overlay').classList.remove('show');
    }
  });

  document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    if (localStorage.getItem('sidebarCollapsed') === 'true' && window.innerWidth >= 768) {
      sidebar.classList.add('collapsed');
    }
  });
</script>
