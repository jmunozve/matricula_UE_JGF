<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validamos si existe el ID de usuario que creas en usuarios_controller.php
if (!isset($_SESSION['id_usuario'])) {
    header("Content-Type: text/html; charset=ISO-8859-1");
    echo "<div style='text-align:center; margin-top:50px; font-family:Arial;'>";
    echo "<h2>🚫 Acceso Denegado</h2>";
    echo "<p>Valentina te informa: Debes iniciar sesión para acceder a este recurso.</p>";
    echo "<a href='/matricula/login.php'>Ir al Inicio de Sesión</a>";
    echo "</div>";
    exit(); // Detiene la carga de cualquier PDF o dato sensible
}