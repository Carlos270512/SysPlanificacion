<?php
require_once __DIR__ . '/../../config/conexion.php';

$id_unidad = intval($_POST['unidad_id'] ?? 0);
$fecha_sabado = $_POST['fecha_sabado'] ?? null;
$objetivo = $_POST['objetivo'] ?? '';
$innovacion = $_POST['innovacion'] ?? '';
$apertura = $_POST['apertura'] ?? '';
$tiempo_apertura = $_POST['tiempo_apertura'] ?? '';
$desarrollo = $_POST['desarrollo'] ?? '';
$tiempo_desarrollo = $_POST['tiempo_desarrollo'] ?? '';
$cierre = $_POST['cierre'] ?? '';
$tiempo_cierre = $_POST['tiempo_cierre'] ?? '';
$trabajo_autonomo = $_POST['trabajo_autonomo'] ?? '';
$fecha_entrega = $_POST['fecha_entrega'] ?? null;

// Validación: verificar que la fecha sea un sábado
if (empty($fecha_sabado)) {
    echo json_encode(['success' => false, 'message' => 'La fecha es requerida.']);
    exit;
}

// Verificar que la fecha sea válida y que sea sábado
$fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_sabado);
if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $fecha_sabado) {
    echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido.']);
    exit;
}

// Verificar que sea sábado (6 = sábado en PHP)
if ($fecha_obj->format('w') != 6) {
    echo json_encode(['success' => false, 'message' => 'La fecha seleccionada debe ser un día sábado.']);
    exit;
}

// Log para debug
error_log("Creando semana_linea - fecha_sabado: $fecha_sabado, fecha_entrega: $fecha_entrega, tiempo_apertura: $tiempo_apertura, trabajo_autonomo: $trabajo_autonomo, apertura: $apertura");

try {
    $stmt = $pdo->prepare("INSERT INTO semana_linea 
        (id_unidad, fecha_sabado, objetivo, innovacion, apertura, tiempo_apertura, desarrollo, tiempo_desarrollo, cierre, tiempo_cierre, trabajo_autonomo, fecha_entrega)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $id_unidad,
        $fecha_sabado,
        $objetivo,
        $innovacion,
        $apertura,
        $tiempo_apertura,
        $desarrollo,
        $tiempo_desarrollo,
        $cierre,
        $tiempo_cierre,
        $trabajo_autonomo,
        $fecha_entrega
    ]);
    $id_semana_linea = $pdo->lastInsertId();
    error_log("Semana_linea creada con ID: $id_semana_linea");
    echo json_encode(['success' => true, 'id_semana_linea' => $id_semana_linea]);
} catch (Exception $e) {
    error_log("Error al crear semana_linea: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

