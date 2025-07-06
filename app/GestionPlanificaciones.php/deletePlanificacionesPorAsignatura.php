<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || empty($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de planificación requerido']);
    exit();
}

try {
    // Incluir conexión
    $pdo = require_once __DIR__ . '/../../config/conexion.php';
    
    $idPlanificacion = $input['id'];
    $usuarioActualizacion = $_SESSION['usuario']['nombre'];
    
    // Verificar que la planificación existe y está activa
    $stmt = $pdo->prepare("SELECT id_planificacion FROM planificaciones WHERE id_planificacion = ? AND estado = 'A'");
    $stmt->execute([$idPlanificacion]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Planificación no encontrada']);
        exit();
    }
    
    // Actualizar el estado a eliminado
    $stmt = $pdo->prepare("
        UPDATE planificaciones 
        SET estado = 'E', 
            usuario_actualizacion = ?,
            fecha_actualizacion = CURRENT_TIMESTAMP
        WHERE id_planificacion = ?
    ");
    
    $resultado = $stmt->execute([$usuarioActualizacion, $idPlanificacion]);
    
    if ($resultado) {
        echo json_encode([
            'success' => true, 
            'message' => 'Planificación eliminada exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al eliminar la planificación'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Error al eliminar planificación: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor'
    ]);
}
?>