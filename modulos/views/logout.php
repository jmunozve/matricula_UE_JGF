<?php
// =================================================================
// modulos/views/logout.php - CIERRE DE SESIÓN SEGURO
// =================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Limpiar todas las variables de la memoria $_SESSION
$_SESSION = array();

// 2. Destruir la cookie de sesión en el navegador (Seguridad adicional)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. Redirigir al archivo login.php que está en la misma carpeta (views)
header("Location: login.php");
exit();
?>