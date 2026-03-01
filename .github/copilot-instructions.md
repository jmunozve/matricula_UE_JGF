# Copilot / AI assistant instructions for "matricula"

Resumen breve
- Proyecto: monolito PHP (sin framework). Vista/Controlador por archivos en `modulos/`, `public/controllers/` y endpoints JS en `ajax/`.
- Entrada principal en tiempo de ejecución: `router.php` (mapa de rutas: `config/routers.php`). Landing: `public/index.php`.

Arquitectura y flujo (rápido)
- Front controller: `router.php` valida sesión/rol, carga `config/routers.php` y resuelve la vista en `modulos/<...>/views/...`.
- Conexión DB: `includes/db.php` → clase `Conexion::abrir()` (devuelve PDO). Muchas controladores usan `Conexion::abrir()` o el `$pdo` global provisto por `includes/init.php`.
- Controladores: scripts PHP procedurales (en `public/controllers/` o `modulos/*/controllers/`) que devuelven HTML/JSON o redirecciones.
- AJAX: endpoints en `ajax/` (JSON) — suele llamarse directamente desde JS en `public/assets/js/`.
- Permisos: `includes/permisos.php` y función `accesoModulo()`; además `router.php` puede usar la clave `roles` definida en `config/routers.php` para restringir rutas.
- Logging: utilidades en `core/OASI.php` (ej. `OASI::registrar()`), y registros en `logs/` (`oasi.log`, `errores.log`, `accesos.log`).

Convenciones del proyecto (importante)
- Rutas: clave string simple (ej. `estudiante/nuevo`). Router rechaza caracteres fuera de [A-Za-z0-9_/-]. Mantén las claves sencillas.
- Mapeo: cada ruta en `config/routers.php` debe incluir al menos `vista` (p. ej. `'mi_modulo/accion' => ['vista' => 'mi_modulo/views/accion', 'titulo'=>'...']`).
- Roles: usa valores en minúscula (`'roles' => ['admin','registro']`).
- Sesión: usuario esperado en `$_SESSION['usuario']` con `id` y `rol`.
- Base de datos: usa consultas preparadas PDO (el proyecto no usa ORM ni migraciones por defecto).
- Archivos estáticos: en `public/assets/` y rutas públicas usan `BASE_URL`/`PATH_ASSETS` definidos en `includes/config.php`.

Cómo hacer cambios comunes (ejemplos)
- Añadir ruta:
```php
// en config/routers.php
'curso/ver' => [
  'vista' => 'curso/views/ver',
  'titulo' => 'Ver curso',
  'roles' => ['admin']
]
```
- Nuevo endpoint AJAX (procedural):
```php
// public/controllers/api/mi_endpoint.php
<?php
require_once __DIR__ . '/../../includes/init.php'; // inicializa $pdo y sesión
$pdo = Conexion::abrir();
$stmt = $pdo->prepare('SELECT id,nombre FROM tabla WHERE activo = 1');
$stmt->execute();
echo json_encode($stmt->fetchAll());
```
- Validación de permisos desde código:
```php
accesoModulo('modulo_clave','accion'); // termina la ejecución si no tiene permiso
```

Dev / debugging
- Dependencias: `composer install` (Composer está en `composer.json`).
- Servidor local: proyecto pensado para XAMPP/Apache con DocumentRoot que exponga `/matricula` (ej. http://localhost/matricula/).
- Configuración básica: ajustar credenciales en `includes/db.php` (no hay archivo .env por defecto).
- Errores & logs: habilitar `display_errors` en dev (`public/index.php` usa `ini_set`), revisar `logs/errores.log` y `logs/oasi.log`.

Prácticas y advertencias (detectables en el código)
- Código procedimental y estado global: muchos scripts dependen de `$_SESSION` y `$pdo` global; preferir minimizar cambios globales y mantener llamadas a `Conexion::abrir()` locales cuando sea posible.
- No hay sistema de migraciones: si añades cambios a la BD, incluye SQL en `docs/` o añade una carpeta `migrations/` con instrucciones claras.
- Mantener vistas separadas en `modulos/*/views/` y evitar lógica compleja mezclada en la vista.

Archivos clave a revisar
- `router.php`, `config/routers.php` (rutas y control de acceso)
- `includes/init.php`, `includes/db.php`, `includes/config.php` (arranque, DB, constantes)
- `includes/permisos.php`, `includes/mensajes.php` (permisos y mensajes UI)
- `modulos/` (vistas y controladores por módulo)
- `public/controllers/`, `ajax/` (endpoints HTTP/AJAX)
- `core/OASI.php`, `logs/` (registro y debugging)

Si no estás seguro / cosas a preguntar
- ¿Debería usar `$pdo` global o `Conexion::abrir()` en este controller? (Preferible usar `Conexion::abrir()` para claridad)
- ¿La ruta debe ser pública o requerirá roles? (comenta la intención en la PR y ajusta `config/routers.php`)

Si quieres, puedo:
- Añadir ejemplos más precisos para un módulo concreto (indica cuál).
- Generar una checklist de PR para este repositorio (tests, migraciones SQL, actualización de rutas, mensajes de logs).

---
> Nota: No encontré instrucciones previas en el repo; si ya tienes un `.github/copilot-instructions.md` o guía interna, indícame y hago un merge conservador para preservar contenido previo.
