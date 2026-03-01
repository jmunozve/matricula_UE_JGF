<?php
// F:\xampp\htdocs\matricula\modulos\controllers\admin\institucion\guardar_institucion.php

header('Content-Type: application/json');

// 1. Inclusión segura de la base de datos
require_once $_SERVER['DOCUMENT_ROOT'] . '/matricula/includes/db.php';

try {
    $pdo = Conexion::abrir();
    
    // 2. Recolección de datos con validación para evitar "Undefined array key"
    // Usamos ?? '' para que si la llave no existe, se asigne un string vacío en lugar de un error
    $id_plantel       = !empty($_POST['id_plantel']) ? $_POST['id_plantel'] : null;
    $nombre_plantel   = $_POST['nombre_plantel'] ?? null;
    $codigo_dea       = $_POST['codigo_dea'] ?? '';
    $periodo_escolar  = $_POST['periodo_escolar'] ?? '';
    $telefono_plantel = $_POST['telefono_plantel'] ?? '';
    $correo_plantel   = $_POST['correo_plantel'] ?? '';
    $direccion_plantel= $_POST['direccion_plantel'] ?? '';

    // Validación básica: Si el nombre está vacío, frenamos el proceso
    if (empty($nombre_plantel)) {
        echo json_encode(['status' => 'error', 'message' => 'El nombre de la institución es obligatorio']);
        exit;
    }

    // 3. Manejo del Logo (Archivo)
    $nombre_logo = null;
    if (isset($_FILES['logo_plantel']) && $_FILES['logo_plantel']['error'] === UPLOAD_ERR_OK) {
        $ruta_subida = $_SERVER['DOCUMENT_ROOT'] . '/matricula/uploads/institucion/';
        
        // Crear carpeta si no existe
        if (!file_exists($ruta_subida)) {
            mkdir($ruta_subida, 0777, true);
        }

        $extension = pathinfo($_FILES['logo_plantel']['name'], PATHINFO_EXTENSION);
        // Nombre único para evitar caché
        $nombre_logo = "logo_" . time() . "." . $extension;
        $ruta_destino = $ruta_subida . $nombre_logo;

        if (!move_uploaded_file($_FILES['logo_plantel']['tmp_name'], $ruta_destino)) {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar la imagen en el servidor']);
            exit;
        }
    }

    // 4. Lógica de SQL (INSERT o UPDATE)
    if ($id_plantel) {
        // ACTUALIZAR REGISTRO EXISTENTE
        $sql = "UPDATE planteles SET 
                nombre_plantel = :nom, 
                codigo_dea = :dea, 
                periodo_escolar = :per, 
                telefono_plantel = :tel, 
                correo_plantel = :cor, 
                direccion_plantel = :dir";
        
        if ($nombre_logo) { 
            $sql .= ", logo_plantel = :logo"; 
        }
        
        $sql .= " WHERE id_plantel = :id";
        
        $stmt = $pdo->prepare($sql);
        $params = [
            ':nom' => $nombre_plantel,
            ':dea' => $codigo_dea,
            ':per' => $periodo_escolar,
            ':tel' => $telefono_plantel,
            ':cor' => $correo_plantel,
            ':dir' => $direccion_plantel,
            ':id'  => $id_plantel
        ];
        if ($nombre_logo) { $params[':logo'] = $nombre_logo; }
        
    } else {
        // INSERTAR NUEVO REGISTRO
        $sql = "INSERT INTO planteles (nombre_plantel, codigo_dea, periodo_escolar, telefono_plantel, correo_plantel, direccion_plantel, logo_plantel) 
                VALUES (:nom, :dea, :per, :tel, :cor, :dir, :logo)";
        
        $stmt = $pdo->prepare($sql);
        $params = [
            ':nom' => $nombre_plantel,
            ':dea' => $codigo_dea,
            ':per' => $periodo_escolar,
            ':tel' => $telefono_plantel,
            ':cor' => $correo_plantel,
            ':dir' => $direccion_plantel,
            ':logo' => ($nombre_logo ? $nombre_logo : 'logo.png')
        ];
    }

    if ($stmt->execute($params)) {
        echo json_encode(['status' => 'success', 'message' => '¡Datos guardados con éxito!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo procesar la solicitud en la base de datos']);
    }

} catch (PDOException $e) {
    // Si hay un error de base de datos, lo capturamos aquí
    echo json_encode(['status' => 'error', 'message' => 'Error de BD: ' . $e->getMessage()]);
}