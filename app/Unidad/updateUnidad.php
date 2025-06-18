<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/conexion.php';

$id_unidad = isset($_POST['id_unidad']) ? intval($_POST['id_unidad']) : null;
$numero_unidad = isset($_POST['numero_unidad']) ? intval($_POST['numero_unidad']) : null;
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$objetivo_unidad = isset($_POST['objetivo_unidad']) ? $_POST['objetivo_unidad'] : '';
$bibliografia = isset($_POST['bibliografia']) ? $_POST['bibliografia'] : '';
$metodologia = isset($_POST['metodologia']) ? $_POST['metodologia'] : '';
$actividades_recuperacion = isset($_POST['actividades_recuperacion']) ? $_POST['actividades_recuperacion'] : '';
$recursos_didacticos = isset($_POST['recursos_didacticos']) ? $_POST['recursos_didacticos'] : '';
$estrategia = isset($_POST['estrategia']) ? $_POST['estrategia'] : '';

// Validación básica
if (!$id_unidad || !$nombre || !$numero_unidad) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
    exit;
}


try {
    $stmt = $pdo->prepare("UPDATE unidad SET 
        numero_unidad = ?, 
        nombre = ?, 
        objetivo_unidad = ?, 
        bibliografia = ?, 
        metodologia = ?, 
        actividades_recuperacion = ?, 
        recursos_didacticos = ?,
        estrategia_ensenanza_aprendizaje = ?
        WHERE id_unidad = ?");
    $stmt->execute([
        $numero_unidad,
        $nombre,
        $objetivo_unidad,
        $bibliografia,
        $metodologia,
        $actividades_recuperacion,
        $recursos_didacticos,
        $estrategia,
        $id_unidad
    ]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
}   