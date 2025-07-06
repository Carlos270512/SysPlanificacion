<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de planificación requerido']);
    exit();
}

try {
    // Incluir conexión
    $pdo = require_once __DIR__ . '/../../config/conexion.php';
    
    $idPlanificacion = $_GET['id'];
    
    // Consultar el archivo
    $stmt = $pdo->prepare("
        SELECT 
            p.archivo_pdf, 
            p.tipo_mime, 
            p.nombre_archivo,
            u.numero_unidad,
            u.nombre as nombre_unidad
        FROM planificaciones p
        INNER JOIN unidad u ON p.unidad_id = u.id_unidad
        WHERE p.id_planificacion = ? AND p.estado = 'A'
    ");
    
    $stmt->execute([$idPlanificacion]);
    $archivo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$archivo) {
        http_response_code(404);
        echo json_encode(['error' => 'Archivo no encontrado']);
        exit();
    }
    
    // Configurar headers para mostrar el PDF
    header('Content-Type: ' . $archivo['tipo_mime']);
    header('Content-Disposition: inline; filename="' . $archivo['nombre_archivo'] . '"');
    header('Content-Length: ' . strlen($archivo['archivo_pdf']));
    
    // Enviar el contenido del archivo
    echo $archivo['archivo_pdf'];
    
} catch (PDOException $e) {
    error_log("Error al obtener planificación: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
?>