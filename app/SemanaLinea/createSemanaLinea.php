<?php
require_once __DIR__ . '/../../config/conexion.php';

$id_unidad = intval($_POST['unidad_id'] ?? 0);
$fecha_sabado = $_POST['fecha_sabado'] ?? null;
$contenido = $_POST['contenido'] ?? '';
$objetivo = $_POST['objetivo'] ?? '';
$innovacion = $_POST['innovacion'] ?? '';
$actividades = $_POST['actividades'] ?? '';
$tiempo_actividades = $_POST['tiempo_actividades'] ?? '';
$desarrollo = $_POST['desarrollo'] ?? '';
$tiempo_desarrollo = $_POST['tiempo_desarrollo'] ?? '';
$cierre = $_POST['cierre'] ?? '';
$tiempo_cierre = $_POST['tiempo_cierre'] ?? '';
$evaluacion_clase = $_POST['evaluacion_clase'] ?? '';
$equipo_herramientas_recursos = $_POST['equipo_herramientas_recursos'] ?? '';
$actividades_refuerzo = $_POST['actividades_refuerzo'] ?? '';

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

try {
    $stmt = $pdo->prepare("INSERT INTO semana_linea 
        (id_unidad, fecha_sabado, contenido, objetivo, innovacion, actividades, tiempo_actividades, desarrollo, tiempo_desarrollo, cierre, tiempo_cierre, evaluacion_clase, equipo_herramientas_recursos, actividades_refuerzo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $id_unidad,
        $fecha_sabado,
        $contenido,
        $objetivo,
        $innovacion,
        $actividades,
        $tiempo_actividades,
        $desarrollo,
        $tiempo_desarrollo,
        $cierre,
        $tiempo_cierre,
        $evaluacion_clase,
        $equipo_herramientas_recursos,
        $actividades_refuerzo
    ]);
    $id_semana_linea = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'id_semana_linea' => $id_semana_linea]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
