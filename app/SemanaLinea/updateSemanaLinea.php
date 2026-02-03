<?php
require_once __DIR__ . '/../../config/conexion.php';

$id_semana_linea = intval($_POST['id_semana_linea'] ?? 0);
$campos = [
    'fecha_sabado', 'contenido', 'objetivo', 'innovacion', 'actividades', 'tiempo_actividades',
    'desarrollo', 'tiempo_desarrollo', 'cierre', 'tiempo_cierre',
    'evaluacion_clase', 'equipo_herramientas_recursos', 'actividades_refuerzo'
];
$set = [];
$params = [];
foreach ($campos as $campo) {
    if (isset($_POST[$campo])) {
        $set[] = "$campo = ?";
        $params[] = $_POST[$campo];
    }
}
$params[] = $id_semana_linea;

if ($set) {
    try {
        $sql = "UPDATE semana_linea SET " . implode(', ', $set) . " WHERE id_semana_linea = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No hay campos para actualizar']);
}