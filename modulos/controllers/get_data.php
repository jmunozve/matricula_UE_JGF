<?php
/**
 * get_data.php / get_ubicacion.php
 * Versión final unificada con estatus='Activo' y salto de plan para Primaria.
 */
header('Content-Type: application/json; charset=utf-8');

// Ajusta la ruta de db.php según la ubicación de este archivo
require_once "../../includes/db.php"; 

try {
    $pdo = Conexion::abrir();
    // Usamos 'type' o 'tipo' para mayor compatibilidad
    $type = $_GET['type'] ?? ($_GET['tipo'] ?? '');
    $id = intval($_GET['id'] ?? 0);

    if (!$id && $type !== 'secciones_todas') {
         echo json_encode([]);
         exit;
    }

    $query = "";
    switch ($type) {
        case 'niveles': 
            // Filtro fundamental: Solo niveles que el Superusuario tenga activos
            $query = "SELECT id_nivel AS id, nombre_nivel AS nombre 
                      FROM niveles_estudio 
                      WHERE id_modalidad = ? AND estatus = 'Activo' 
                      ORDER BY id_nivel ASC"; 
            break;

        case 'planes': 
            // Según tu vista SQL, la columna es 'descripcion'
            $query = "SELECT id_plan AS id, descripcion AS nombre 
                      FROM planes_estudio 
                      WHERE id_nivel = ? AND estatus = 'Activo' 
                      ORDER BY descripcion ASC"; 
            break;

        case 'grados': 
            /**
             * LÓGICA INTELIGENTE PARA PRIMARIA:
             * Buscamos los grados directamente por el ID del Nivel haciendo un JOIN con Planes.
             * Esto evita que el usuario tenga que seleccionar el Plan manualmente.
             */
            $query = "SELECT g.id_grado AS id, g.nombre_grado AS nombre 
                      FROM grados g
                      INNER JOIN planes_estudio p ON g.id_plan = p.id_plan
                      WHERE p.id_nivel = ? AND p.estatus = 'Activo' 
                      ORDER BY g.id_grado ASC"; 
            break;

        case 'secciones':  
            // Concatenamos para mostrar información clara en el select
            $query = "SELECT id_seccion AS id, CONCAT('Secc: ', letra, ' - ', turno) AS nombre 
                      FROM secciones 
                      WHERE id_grado = ? AND estatus = 'Activo' 
                      ORDER BY letra ASC"; 
            break;

        case 'areas':   
            $query = "SELECT id_area AS id, nombre_area AS nombre 
                      FROM areas_formacion 
                      WHERE id_grado = ? 
                      ORDER BY nombre_area ASC"; 
            break;

        case 'municipios':
            $query = "SELECT id_municipio AS id, nombre_municipio AS nombre 
                      FROM municipios WHERE id_estado = ? ORDER BY nombre_municipio ASC";
            break;

        case 'parroquias':
            $query = "SELECT id_parroquia AS id, nombre_parroquia AS nombre 
                      FROM parroquias WHERE id_municipio = ? ORDER BY nombre_parroquia ASC";
            break;
            
        default:
            echo json_encode(["error" => "Tipo '$type' no reconocido"]);
            exit;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($resultados);

} catch (Exception $e) {
    // Enviamos el mensaje de error real para facilitar el debug
    echo json_encode(["error" => "Error de servidor: " . $e->getMessage()]);
}