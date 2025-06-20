<?php
require_once __DIR__ . '/../../config/conexion.php';

$id_unidad = intval($_POST['unidad_id'] ?? 0);
$fecha_sabado = $_POST['fecha_sabado'] ?? null;
$contenido = $_POST['contenido'] ?? '';
$objetivo = $_POST['objetivo'] ?? '';
$actividades = $_POST['actividades'] ?? '';
$tiempo_actividades = $_POST['tiempo_actividades'] ?? '';
$desarrollo = $_POST['desarrollo'] ?? '';
$tiempo_desarrollo = $_POST['tiempo_desarrollo'] ?? '';
$cierre = $_POST['cierre'] ?? '';
$tiempo_cierre = $_POST['tiempo_cierre'] ?? '';
$evaluacion_clase = $_POST['evaluacion_clase'] ?? '';
$equipo_herramientas_recursos = $_POST['equipo_herramientas_recursos'] ?? '';
$actividades_refuerzo = $_POST['actividades_refuerzo'] ?? '';

try {
    $stmt = $pdo->prepare("INSERT INTO semana_linea 
        (id_unidad, fecha_sabado, contenido, objetivo, actividades, tiempo_actividades, desarrollo, tiempo_desarrollo, cierre, tiempo_cierre, evaluacion_clase, equipo_herramientas_recursos, actividades_refuerzo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $id_unidad, $fecha_sabado, $contenido, $objetivo, $actividades, $tiempo_actividades,
        $desarrollo, $tiempo_desarrollo, $cierre, $tiempo_cierre, $evaluacion_clase,
        $equipo_herramientas_recursos, $actividades_refuerzo
    ]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}