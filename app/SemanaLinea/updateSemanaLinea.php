<?php
require_once __DIR__ . '/../../config/conexion.php';

$id_semana_linea = intval($_POST['id_semana_linea'] ?? 0);
$campos = [
    'fecha_sabado', 'contenido', 'tema_clase_SAnterior', 'Atividades_previas_clase', 'tiempo_actividades_previas_clase',
    'objetivo', 'innovacion', 'apertura', 'tiempo_apertura',
    'desarrollo', 'tiempo_desarrollo', 'cierre', 'tiempo_cierre',
    'trabajo_autonomo', 'fecha_entrega'
];
$set = [];
$params = [];

// Log para debug
error_log("=== UPDATE SEMANA_LINEA ===");
error_log("id_semana_linea: $id_semana_linea");
error_log("POST completo: " . print_r($_POST, true));

foreach ($campos as $campo) {
    if (isset($_POST[$campo])) {
        $set[] = "$campo = ?";
        $params[] = $_POST[$campo];
        $valorLog = is_string($_POST[$campo]) ? substr($_POST[$campo], 0, 100) : $_POST[$campo];
        error_log("  ✓ Actualizando $campo (len=" . strlen($_POST[$campo]) . "): $valorLog");
    }
}
$params[] = $id_semana_linea;

if ($set) {
    try {
        $sql = "UPDATE semana_linea SET " . implode(', ', $set) . " WHERE id_semana_linea = ?";
        error_log("SQL: $sql");
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        error_log("Update exitoso. Filas afectadas: " . $stmt->rowCount());
        echo json_encode(['success' => true, 'rowCount' => $stmt->rowCount()]);
    } catch (Exception $e) {
        error_log("Error en UPDATE: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    error_log("No hay campos para actualizar");
    echo json_encode(['success' => false, 'message' => 'No hay campos para actualizar']);
}
