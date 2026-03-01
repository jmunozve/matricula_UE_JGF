<?php
header('Content-Type: application/json');
require_once "../../../includes/db.php"; 

try {
    $pdo = Conexion::abrir();

    $cedula = isset($_GET['cedula']) ? trim($_GET['cedula']) : '';
    // Si no viene tipo, asumimos 'V' por defecto
    $tipo   = isset($_GET['tipo']) ? trim($_GET['tipo']) : 'V';

    if (empty($cedula)) {
        echo json_encode(['encontrado' => false, 'error' => 'Cédula no proporcionada']);
        exit;
    }

    // Buscamos al representante
    $stmt = $pdo->prepare("SELECT * FROM representantes WHERE cedula_rep = ? AND tipo_doc_rep = ? LIMIT 1");
    $stmt->execute([$cedula, $tipo]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($r) {
        /** * LÓGICA DE PREFIJO (GEMELOS) 
         * Cuenta hijos para sugerir el prefijo de la cédula escolar
         */
        $stmtHijos = $pdo->prepare("SELECT COUNT(*) as total FROM estudiantes WHERE id_representante = ?");
        $stmtHijos->execute([$r['id_representante']]);
        $conteoHijos = (int)$stmtHijos->fetch(PDO::FETCH_ASSOC)['total'];
        
        $sugerenciaPrefijo = $conteoHijos + 1;

        // Devolvemos TODO el objeto para que el JS pueda comparar qué falta
        echo json_encode([
            'encontrado'         => true,
            'id_representante'   => $r['id_representante'],
            'nombres'            => $r['nombre_rep'] ?? '',
            'apellidos'          => $r['apellido_rep'] ?? '',
            'sexo'               => $r['sexo_rep'] ?? '',         
            'fecha_nac'          => $r['fecha_nac_rep'] ?? '',    
            'id_pais'            => $r['id_pais_rep'] ?? '', 
            'id_estado'          => $r['id_estado_rep'] ?? '',
            'id_municipio'       => $r['id_municipio_rep'] ?? '',
            'id_parroquia'       => $r['id_parroquia_rep'] ?? '',
            'direccion'          => $r['direccion_detalle_rep'] ?? '', 
            'telefono'           => $r['tel_rep'] ?? '',
            'correo'             => $r['correo_rep'] ?? '',
            'parentesco'         => $r['parentesco_rep'] ?? '',   
            'sugerencia_prefijo' => $sugerenciaPrefijo,           
            'foto'               => $r['foto_carnet_rep'] ?? '',
            'pdf_cedula'         => $r['pdf_cedula_rep'] ?? ''
        ]);
    } else {
        echo json_encode(['encontrado' => false]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'encontrado' => false, 
        'error' => 'Error de BD: ' . $e->getMessage()
    ]);
}