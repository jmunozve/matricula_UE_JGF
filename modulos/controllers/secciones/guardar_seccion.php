<?php
/**
 * modulos/controllers/secciones/guardar_seccion.php
 * Controlador para guardar una nueva sección con auditoría y redirección a lista de estudiantes
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "../../../includes/db.php";

$pdo = Conexion::abrir();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Identificación del Usuario
    $id_usuario_sesion = $_SESSION['id'] ?? $_SESSION['id_usuario'] ?? null;

    if (!$id_usuario_sesion) {
        $_SESSION['swal'] = [
            'icon'  => 'error',
            'title' => 'Sesión no válida',
            'text'  => 'No se pudo identificar al usuario creador. Inicie sesión nuevamente.',
        ];
        header("Location: ../../../login.php");
        exit;
    }

    // 2. Capturar y Sanitizar Datos
    $id_modalidad  = $_POST['id_modalidad'] ?? '';
    $id_nivel      = $_POST['id_nivel']     ?? '';
    $id_plan       = $_POST['id_plan']      ?? '';
    $id_grado      = $_POST['id_grado']     ?? ''; // Este es el ID clave
    $turno         = $_POST['turno']        ?? 'Mañana';
    $letra_seccion = isset($_POST['letra_seccion']) ? trim(strtoupper($_POST['letra_seccion'])) : '';
    
    $tiene_prof    = ($_POST['tiene_profesor'] ?? '0') === '1';
    $id_docente    = ($tiene_prof && !empty($_POST['id_docente'])) ? $_POST['id_docente'] : null;

    // Ajustamos parámetros de retorno para que 'id_grado' persista si hubo error
    $params_retorno = "?modalidad=" . urlencode($id_modalidad) . 
                      "&nivel=" . urlencode($id_nivel) . 
                      "&plan=" . urlencode($id_plan) . 
                      "&id_grado=" . urlencode($id_grado);

    // 3. Validación de campos obligatorios
    if (empty($id_grado) || empty($letra_seccion) || empty($turno)) {
        $_SESSION['swal'] = [
            'icon'  => 'error',
            'title' => '¡Faltan datos!',
            'text'  => 'El grado, la letra de sección y el turno son campos obligatorios.',
            'timer' => 3500
        ];
        header("Location: ../../views/secciones/crear_seccion.php" . $params_retorno);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 4. Verificar duplicados
        $check = $pdo->prepare("SELECT id_seccion FROM secciones 
                                WHERE id_grado = ? AND letra = ? AND turno = ? AND estatus = 'Activo'");
        $check->execute([$id_grado, $letra_seccion, $turno]);
        
        if ($check->fetch()) {
            $pdo->rollBack();
            $_SESSION['swal'] = [
                'icon'  => 'warning',
                'title' => '¡Sección ya registrada!',
                'html'  => "La sección <b>{$letra_seccion}</b> ya existe para este grado en el turno <b>{$turno}</b>.",
                'confirmButtonColor' => '#0d6efd'
            ];
            header("Location: ../../views/secciones/crear_seccion.php" . $params_retorno);
            exit;
        }

        // 5. Insertar Sección
        // Nota: Asegúrate de que 'id_usuario_creador' exista en tu tabla 'secciones'
        $sql = "INSERT INTO secciones (id_grado, letra, turno, id_docente, estatus, fecha_creacion, id_usuario_creador) 
                VALUES (:grado, :letra, :turno, :docente, 'Activo', NOW(), :creador)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':grado'   => $id_grado,
            ':letra'   => $letra_seccion,
            ':turno'   => $turno,
            ':docente' => $id_docente,
            ':creador' => $id_usuario_sesion
        ]);

        $id_nueva_seccion = $pdo->lastInsertId();

        // 6. Historial Docente
        if ($id_docente) {
            $historial = $pdo->prepare("INSERT INTO historial_docentes_secciones 
                                        (id_seccion, id_docente, fecha_asignacion) 
                                        VALUES (?, ?, NOW())");
            $historial->execute([$id_nueva_seccion, $id_docente]);
        }

        $pdo->commit();

        // 7. Configurar éxito y REDIRIGIR A LISTA DE ESTUDIANTES
        $_SESSION['swal'] = [
            'icon'     => 'success',
            'title'    => '¡Sección Creada!',
            'html'     => "La sección <b>{$letra_seccion}</b> se creó con éxito. Ya puede inscribir estudiantes.",
            'timer'    => 3000,
            'toast'    => false,
            'position' => 'center'
        ];

        // Redirección a la vista de estudiantes pasando el ID de la nueva sección
        header("Location: ../../views/secciones/lista.php?status=success");
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();

        $msg = ($e->getCode() == 23000) ? "Error de integridad: Datos duplicados o referencias inválidas." : $e->getMessage();
        
        $_SESSION['swal'] = [
            'icon'  => 'error',
            'title' => 'Error de Base de Datos',
            'text'  => $msg
        ];
        header("Location: ../../views/secciones/crear_seccion.php" . $params_retorno);
        exit;
    }

} else {
    header("Location: ../../views/secciones/crear_seccion.php");
    exit;
}