<?php
/**
 * ARCHIVO: obtener_personal.php
 * DESCRIPCIÓN: Consulta la tabla 'personal' con JOIN a 'especialidades' y formatea para DataTables.
 */

if (ob_get_length()) ob_clean();

require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = Conexion::abrir();
    
    // CONSULTA ACTUALIZADA: JOIN con la tabla especialidades
    $sql = "SELECT p.*, e.nombre_especialidad 
            FROM personal p 
            LEFT JOIN especialidades e ON p.id_especialidad = e.id_especialidad 
            ORDER BY FIELD(p.cargo, 'Directivo', 'Docente', 'Administrativo', 'Obrero'), p.apellido ASC";
            
    $stmt = $pdo->query($sql);
    $personal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];

    foreach ($personal as $p) {
        // Colores según el Cargo
        $badgeColor = 'bg-secondary';
        switch ($p['cargo']) {
            case 'Directivo':      $badgeColor = 'bg-dark'; break;
            case 'Docente':        $badgeColor = 'bg-primary'; break;
            case 'Administrativo': $badgeColor = 'bg-info text-dark'; break;
            case 'Obrero':         $badgeColor = 'bg-warning text-dark'; break;
        }

        // Estilo del Estatus
        $estatusBadge = ($p['estatus'] == 'Activo') 
            ? '<span class="badge rounded-pill bg-success px-3">Activo</span>' 
            : '<span class="badge rounded-pill bg-danger px-3">Inactivo</span>';

        // Preparamos la fila
        $data[] = [
            "cedula"          => "<strong>" . htmlspecialchars($p['cedula']) . "</strong>",
            "nombre_completo" => mb_strtoupper($p['apellido'] . ", " . $p['nombre'], 'UTF-8'),
            "cargo_html"      => "<span class='badge $badgeColor'>" . $p['cargo'] . "</span>",
            
            // CAMBIO: Ahora usamos 'nombre_especialidad' que viene del JOIN
            "especialidad"    => !empty($p['nombre_especialidad']) 
                                 ? htmlspecialchars($p['nombre_especialidad']) 
                                 : '<span class="text-muted small">Sin definir</span>',
                                 
            "estatus_html"    => $estatusBadge,
            "acciones"        => '
                <div class="btn-group shadow-sm">
                    <button class="btn btn-sm btn-outline-primary" title="Editar" onclick=\'editarPersonal(' . json_encode($p) . ')\'>
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" title="Alternar Estatus" onclick="cambiarEstatus(' . $p['id_docente'] . ', \'' . $p['estatus'] . '\')">
                        <i class="bi bi-power"></i>
                    </button>
                </div>'
        ];
    }

    echo json_encode(["data" => $data], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "data" => [],
        "error" => "Error de base de datos: " . $e->getMessage()
    ]);
}