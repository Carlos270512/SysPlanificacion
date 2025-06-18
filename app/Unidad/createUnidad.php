<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/conexion.php';
// Recoger datos del formulario
$asignatura_codigo = isset($_POST['asignatura_codigo']) ? trim($_POST['asignatura_codigo']) : '';
$numero_unidad = isset($_POST['numero_unidad']) ? intval($_POST['numero_unidad']) : null;
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$objetivo_unidad = isset($_POST['objetivo_unidad']) ? $_POST['objetivo_unidad'] : '';
$bibliografia = isset($_POST['bibliografia']) ? $_POST['bibliografia'] : '';
$metodologia = isset($_POST['metodologia']) ? $_POST['metodologia'] : '';
$actividades_recuperacion = isset($_POST['actividades_recuperacion']) ? $_POST['actividades_recuperacion'] : '';
$recursos_didacticos = isset($_POST['recursos_didacticos']) ? $_POST['recursos_didacticos'] : '';
$estrategia = isset($_POST['estrategia']) ? $_POST['estrategia'] : '';
$semana_inicio = isset($_POST['semana_inicio']) ? $_POST['semana_inicio'] : null;
$semana_fin = isset($_POST['semana_fin']) ? $_POST['semana_fin'] : null;

// Validaciones básicas

// Validaciones básicas
if (!$asignatura_codigo || !$nombre || !$numero_unidad) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO unidad 
        (asignatura_codigo, numero_unidad, nombre, objetivo_unidad, bibliografia, metodologia, actividades_recuperacion, recursos_didacticos, estrategia_ensenanza_aprendizaje, semana_inicio, semana_fin) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $asignatura_codigo,
        $numero_unidad,
        $nombre,
        $objetivo_unidad,
        $bibliografia,
        $metodologia,
        $actividades_recuperacion,
        $recursos_didacticos,
        $estrategia, // <--- Aquí se guarda en la columna correcta
        $semana_inicio,
        $semana_fin
    ]);
    $unidad_id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'unidad_id' => $unidad_id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
}