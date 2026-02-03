<?php
require_once __DIR__ . '/../../config/conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semana_id = $_POST['semana_id'] ?? null;
    $campo = $_POST['campo'] ?? null;
    $valor = $_POST['valor'] ?? null;

    if (!$semana_id || !$campo) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }

    $campos_permitidos = [
        'fecha_semana','semana_fin', // <-- Asegúrate de incluirlo aquí
        'actividades_previas', 'tiempo_actividades_previas', 'contenido',
        'objetivo_lunes', 'innovacion_lunes', 'tiempo_objetivo_lunes', 'apertura_lunes', 'tiempo_apertura_lunes', 'desarrollo_lunes', 'tiempo_desarrollo_lunes', 'cierre_lunes', 'tiempo_cierre_lunes', 'trabajo_autonomo_lunes', 'fecha_entrega_lunes',
        'objetivo_martes', 'innovacion_martes', 'tiempo_objetivo_martes', 'apertura_martes', 'tiempo_apertura_martes', 'desarrollo_martes', 'tiempo_desarrollo_martes', 'cierre_martes', 'tiempo_cierre_martes', 'trabajo_autonomo_martes', 'fecha_entrega_martes',
        'objetivo_miercoles', 'innovacion_miercoles', 'tiempo_objetivo_miercoles', 'apertura_miercoles', 'tiempo_apertura_miercoles', 'desarrollo_miercoles', 'tiempo_desarrollo_miercoles', 'cierre_miercoles', 'tiempo_cierre_miercoles', 'trabajo_autonomo_miercoles', 'fecha_entrega_miercoles',
        'objetivo_jueves', 'innovacion_jueves', 'tiempo_objetivo_jueves', 'apertura_jueves', 'tiempo_apertura_jueves', 'desarrollo_jueves', 'tiempo_desarrollo_jueves', 'cierre_jueves', 'tiempo_cierre_jueves', 'trabajo_autonomo_jueves', 'fecha_entrega_jueves',
        'objetivo_viernes', 'innovacion_viernes', 'tiempo_objetivo_viernes', 'apertura_viernes', 'tiempo_apertura_viernes', 'desarrollo_viernes', 'tiempo_desarrollo_viernes', 'cierre_viernes', 'tiempo_cierre_viernes', 'trabajo_autonomo_viernes', 'fecha_entrega_viernes'
    ];

    if (!in_array($campo, $campos_permitidos)) {
        echo json_encode(['success' => false, 'message' => 'Campo no permitido']);
        exit;
    }

    $sql = "UPDATE semana SET $campo = :valor WHERE id_semana = :semana_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':semana_id', $semana_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Método no permitido']);