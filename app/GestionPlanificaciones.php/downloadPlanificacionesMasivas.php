<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ids']) || empty($input['ids']) || !is_array($input['ids'])) {
    http_response_code(400);
    echo json_encode(['error' => 'IDs de planificaciones requeridos']);
    exit();
}

try {
    // Incluir conexión
    $pdo = require_once __DIR__ . '/../../config/conexion.php';
    
    $ids = array_map('intval', $input['ids']); // Sanitizar IDs
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    // Consultar las planificaciones seleccionadas
    $stmt = $pdo->prepare("
        SELECT 
            p.id_planificacion,
            p.archivo_pdf, 
            p.nombre_archivo,
            u.numero_unidad,
            u.nombre as nombre_unidad,
            a.nombre_asignatura,
            a.codigo as codigo_asignatura
        FROM planificaciones p
        INNER JOIN unidad u ON p.unidad_id = u.id_unidad
        INNER JOIN asignatura a ON u.asignatura_codigo = a.codigo
        WHERE p.id_planificacion IN ($placeholders) AND p.estado = 'A'
        ORDER BY u.numero_unidad
    ");
    
    $stmt->execute($ids);
    $planificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($planificaciones)) {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontraron planificaciones válidas']);
        exit();
    }
    
    // Crear archivo ZIP temporal
    $zipFileName = 'planificaciones_' . date('Y-m-d_H-i-s') . '.zip';
    $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipFileName;
    
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo crear el archivo ZIP']);
        exit();
    }
    
    // Agregar archivos al ZIP
    foreach ($planificaciones as $planificacion) {
        // Crear nombre de archivo único para evitar conflictos
        $nombreArchivo = sprintf(
            "Unidad_%02d_%s_%s",
            $planificacion['numero_unidad'],
            preg_replace('/[^a-zA-Z0-9_-]/', '_', $planificacion['nombre_unidad']),
            $planificacion['nombre_archivo']
        );
        
        // Agregar el PDF al ZIP
        $zip->addFromString($nombreArchivo, $planificacion['archivo_pdf']);
    }
    
    // Agregar un archivo de información
    $infoContent = "INFORMACIÓN DE DESCARGA\n";
    $infoContent .= "========================\n\n";
    $infoContent .= "Asignatura: " . $planificaciones[0]['nombre_asignatura'] . "\n";
    $infoContent .= "Código: " . $planificaciones[0]['codigo_asignatura'] . "\n";
    $infoContent .= "Fecha de descarga: " . date('d/m/Y H:i:s') . "\n";
    $infoContent .= "Usuario: " . $_SESSION['usuario']['nombre'] . "\n";
    $infoContent .= "Total de archivos: " . count($planificaciones) . "\n\n";
    $infoContent .= "ARCHIVOS INCLUIDOS:\n";
    $infoContent .= "==================\n";
    
    foreach ($planificaciones as $planificacion) {
        $infoContent .= "- Unidad " . $planificacion['numero_unidad'] . ": " . $planificacion['nombre_archivo'] . "\n";
    }
    
    $zip->addFromString('INFO_DESCARGA.txt', $infoContent);
    
    // Cerrar el ZIP
    $zip->close();
    
    // Configurar headers para descarga
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Enviar el archivo
    readfile($zipPath);
    
    // Eliminar archivo temporal
    unlink($zipPath);
    
} catch (PDOException $e) {
    error_log("Error en descarga masiva: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
} catch (Exception $e) {
    error_log("Error general en descarga masiva: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear el archivo ZIP']);
}
?>