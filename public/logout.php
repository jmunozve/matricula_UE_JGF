<?php
session_start();

// 🧹 Destruir sesión
$_SESSION = [];
session_unset();
session_destroy();

// 🕵️‍♂️ Logging técnico (opcional)
$logPath = __DIR__ . '/../logs/logout.log';
$usuario = $_SESSION['usuario']['usuario'] ?? 'desconocido';
$timestamp = date('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'IP desconocida';
$log = "[{$timestamp}] Logout de usuario: {$usuario} desde IP: {$ip}\n";
@file_put_contents($logPath, $log, FILE_APPEND);

// 🚪 Redirigir al login
header("Location: /matricula/modulos/autenticacion/views/login.php?mensaje=Sesión finalizada");
exit;
