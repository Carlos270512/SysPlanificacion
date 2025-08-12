<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Verificar que el usuario esté logueado como coordinador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'COORDINADOR') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit();
}

// Verificar que se recibió el parámetro docente_codigo
if (!isset($_GET['docente_codigo']) || empty($_GET['docente_codigo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetro docente_codigo requerido']);
    exit();
}

try {
    // Conexión a la base de datos
    $pdo = require_once __DIR__ . '/../../config/conexion.php';
    require_once __DIR__ . '/coevaluacion_Repository.php';
    
    // Inicializar repository
    $repo = new CoevaluacionRepository($pdo);
    
    // Obtener código del docente
    $docenteCodigo = $_GET['docente_codigo'];
    
    // Validar que el docente existe y está activo
    $docente = $repo->getDocentePorCodigo($docenteCodigo);
    if (!$docente) {
        http_response_code(404);
        echo json_encode(['error' => 'Docente no encontrado o inactivo']);
        exit();
    }
    
    // Obtener asignaturas del docente
    $asignaturas = $repo->getAsignaturasPorDocente($docenteCodigo);
    
    // Devolver respuesta exitosa en formato JSON
    echo json_encode($asignaturas);
    
} catch (PDOException $e) {
    error_log("Error de base de datos en getAsignaturas.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor - Base de datos']);
    
} catch (Exception $e) {
    error_log("Error general en getAsignaturas.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
?>