<?php
require_once __DIR__ . '/../../config/conexion.php';

$id_unidad = intval($_POST['unidad_id'] ?? 0);
$fecha_sabado = $_POST['fecha_sabado'] ?? null;
$tema_clase_SAnterior = $_POST['tema_clase_SAnterior'] ?? '';
$Atividades_previas_clase = $_POST['Atividades_previas_clase'] ?? '';
$tiempo_actividades_previas_clase = $_POST['tiempo_actividades_previas_clase'] ?? '';
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
error_log("=== CREANDO SEMANA_LINEA ===");
error_log("POST completo: " . print_r($_POST, true));
error_log("---");
error_log("fecha_sabado: " . ($fecha_sabado ?? 'NULL'));
error_log("tema_clase_SAnterior (len=" . strlen($tema_clase_SAnterior) . "): " . substr($tema_clase_SAnterior, 0, 150));
error_log("Atividades_previas_clase (len=" . strlen($Atividades_previas_clase) . "): " . substr($Atividades_previas_clase, 0, 150));
error_log("tiempo_actividades_previas_clase: " . ($tiempo_actividades_previas_clase ?? 'EMPTY'));
error_log("objetivo (len=" . strlen($objetivo) . "): " . substr($objetivo, 0, 150));
error_log("===========================");

try {
    $stmt = $pdo->prepare("INSERT INTO semana_linea 
        (id_unidad, fecha_sabado, tema_clase_SAnterior, Atividades_previas_clase, tiempo_actividades_previas_clase, objetivo, innovacion, apertura, tiempo_apertura, desarrollo, tiempo_desarrollo, cierre, tiempo_cierre, trabajo_autonomo, fecha_entrega)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $id_unidad,
        $fecha_sabado,
        $tema_clase_SAnterior,
        $Atividades_previas_clase,
        $tiempo_actividades_previas_clase,
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

