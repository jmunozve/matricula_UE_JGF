<?function ruta($clave) {
  $rutas = require __DIR__ . '/routers.php';
  $page = $rutas[$clave] ?? null;

  if (!$page) {
    return 'router.php?ruta=error/404';
  }

  return 'router.php?ruta=' . urlencode($clave);
}
