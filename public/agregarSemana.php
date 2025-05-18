<?php
header('Content-Type: application/json');
$pdo = require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_unidad = $_POST['id_unidad'] ?? null;
    $fecha_semana = $_POST['fecha_semana'] ?? null;
    $actividades_previas = $_POST['actividades_previas'] ?? null;
    $contenido = $_POST['contenido'] ?? null;

    $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
    $campos = [];
    $valores = [];

    foreach ($dias as $dia) {
        $campos[] = "fecha_$dia";
        $campos[] = "objetivo_$dia";
        $campos[] = "tiempo_objetivo_$dia";
        $campos[] = "apertura_$dia";
        $campos[] = "tiempo_apertura_$dia";
        $campos[] = "desarrollo_$dia";
        $campos[] = "tiempo_desarrollo_$dia";
        $campos[] = "cierre_$dia";
        $campos[] = "tiempo_cierre_$dia";
        $campos[] = "trabajo_autonomo_$dia";
        $campos[] = "fecha_entrega_$dia";
    }

    foreach ($dias as $dia) {
        $valores[] = $_POST["fecha_$dia"] !== '' ? $_POST["fecha_$dia"] : null;
        $valores[] = $_POST["objetivo_$dia"] !== '' ? $_POST["objetivo_$dia"] : null;
        $valores[] = $_POST["tiempo_objetivo_$dia"] !== '' ? $_POST["tiempo_objetivo_$dia"] : null;
        $valores[] = $_POST["apertura_$dia"] !== '' ? $_POST["apertura_$dia"] : null;
        $valores[] = $_POST["tiempo_apertura_$dia"] !== '' ? $_POST["tiempo_apertura_$dia"] : null;
        $valores[] = $_POST["desarrollo_$dia"] !== '' ? $_POST["desarrollo_$dia"] : null;
        $valores[] = $_POST["tiempo_desarrollo_$dia"] !== '' ? $_POST["tiempo_desarrollo_$dia"] : null;
        $valores[] = $_POST["cierre_$dia"] !== '' ? $_POST["cierre_$dia"] : null;
        $valores[] = $_POST["tiempo_cierre_$dia"] !== '' ? $_POST["tiempo_cierre_$dia"] : null;
        $valores[] = $_POST["trabajo_autonomo_$dia"] !== '' ? $_POST["trabajo_autonomo_$dia"] : null;
        $valores[] = $_POST["fecha_entrega_$dia"] !== '' ? $_POST["fecha_entrega_$dia"] : null;
    }

    $tiempo_actividades_previas = $_POST['tiempo_actividades_previas'] ?? null;

    if ($id_unidad && $fecha_semana) {
        $sql = "INSERT INTO semana (
            id_unidad, fecha_semana, actividades_previas, tiempo_actividades_previas, contenido,
            " . implode(',', $campos) . "
        ) VALUES (
            ?, ?, ?, ?, ?," . str_repeat('?,', count($campos) - 1) . "?
        )";
        $params = array_merge(
            [$id_unidad, $fecha_semana, $actividades_previas, $tiempo_actividades_previas, $contenido],
            $valores
        );
        $stmt = $pdo->prepare($sql);
        try {
            $ok = $stmt->execute($params);
            echo json_encode(['success' => $ok]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
exit;
