<?php
require_once __DIR__ . '/../../config/conexion.php';

$id_unidad = intval($_POST['unidad_id'] ?? 0);
$fecha_sabado = $_POST['fecha_sabado'] ?? null;

// --- NUEVO: Verificar si debe copiar datos de semana_linea base ---
$semana_linea_base = null;

// Verificar datos de la unidad actual
$stmtCheckBase = $pdo->prepare("SELECT unidad_base, asignatura_codigo FROM unidad WHERE id_unidad = ?");
$stmtCheckBase->execute([$id_unidad]);
$unidadActual = $stmtCheckBase->fetch(PDO::FETCH_ASSOC);

if ($unidadActual) {
    // PASO 1: Intentar buscar la ÚLTIMA semana creada en la MISMA unidad
    $stmtUltimaSemana = $pdo->prepare("SELECT * FROM semana_linea 
                                        WHERE id_unidad = ? 
                                        ORDER BY fecha_sabado DESC 
                                        LIMIT 1");
    $stmtUltimaSemana->execute([$id_unidad]);
    $semana_linea_base = $stmtUltimaSemana->fetch(PDO::FETCH_ASSOC);
    
    // PASO 2: Si no hay semanas en esta unidad, buscar semana de la Unidad Base
    if (!$semana_linea_base && $unidadActual['unidad_base'] != 1) {
        $asignatura_codigo = $unidadActual['asignatura_codigo'];
        
        // Buscar la unidad base de esta asignatura
        $stmtUnidadBase = $pdo->prepare("SELECT id_unidad FROM unidad 
                                          WHERE asignatura_codigo = ? 
                                          AND numero_unidad = 1 
                                          AND unidad_base = 1 
                                          LIMIT 1");
        $stmtUnidadBase->execute([$asignatura_codigo]);
        $unidadBase = $stmtUnidadBase->fetch(PDO::FETCH_ASSOC);
        
        if ($unidadBase) {
            $id_unidad_base = $unidadBase['id_unidad'];
            
            // Buscar la primera semana_linea de la unidad base
            $stmtSemanaBase = $pdo->prepare("SELECT * FROM semana_linea 
                                             WHERE id_unidad = ? 
                                             ORDER BY fecha_sabado ASC 
                                             LIMIT 1");
            $stmtSemanaBase->execute([$id_unidad_base]);
            $semana_linea_base = $stmtSemanaBase->fetch(PDO::FETCH_ASSOC);
        }
    }
}
// --- FIN NUEVO ---

// Si hay semana_linea base, usar sus datos como valores por defecto
if ($semana_linea_base) {
    $contenido = $_POST['contenido'] ?? $semana_linea_base['contenido'];
    $tema_clase_SAnterior = $_POST['tema_clase_SAnterior'] ?? $semana_linea_base['tema_clase_SAnterior'];
    $Atividades_previas_clase = $_POST['Atividades_previas_clase'] ?? $semana_linea_base['Atividades_previas_clase'];
    $tiempo_actividades_previas_clase = $_POST['tiempo_actividades_previas_clase'] ?? $semana_linea_base['tiempo_actividades_previas_clase'];
    $objetivo = $_POST['objetivo'] ?? $semana_linea_base['objetivo'];
    $innovacion = $_POST['innovacion'] ?? $semana_linea_base['innovacion'];
    $apertura = $_POST['apertura'] ?? $semana_linea_base['apertura'];
    $tiempo_apertura = $_POST['tiempo_apertura'] ?? $semana_linea_base['tiempo_apertura'];
    $desarrollo = $_POST['desarrollo'] ?? $semana_linea_base['desarrollo'];
    $tiempo_desarrollo = $_POST['tiempo_desarrollo'] ?? $semana_linea_base['tiempo_desarrollo'];
    $cierre = $_POST['cierre'] ?? $semana_linea_base['cierre'];
    $tiempo_cierre = $_POST['tiempo_cierre'] ?? $semana_linea_base['tiempo_cierre'];
    $trabajo_autonomo = $_POST['trabajo_autonomo'] ?? $semana_linea_base['trabajo_autonomo'];
    $fecha_entrega = $_POST['fecha_entrega'] ?? null;
} else {
    $contenido = $_POST['contenido'] ?? '';
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
}

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
error_log("contenido (len=" . strlen($contenido) . "): " . substr($contenido, 0, 150));
error_log("tema_clase_SAnterior (len=" . strlen($tema_clase_SAnterior) . "): " . substr($tema_clase_SAnterior, 0, 150));
error_log("Atividades_previas_clase (len=" . strlen($Atividades_previas_clase) . "): " . substr($Atividades_previas_clase, 0, 150));
error_log("tiempo_actividades_previas_clase: " . ($tiempo_actividades_previas_clase ?? 'EMPTY'));
error_log("objetivo (len=" . strlen($objetivo) . "): " . substr($objetivo, 0, 150));
error_log("===========================");

try {
    $stmt = $pdo->prepare("INSERT INTO semana_linea 
        (id_unidad, fecha_sabado, contenido, tema_clase_SAnterior, Atividades_previas_clase, tiempo_actividades_previas_clase, objetivo, innovacion, apertura, tiempo_apertura, desarrollo, tiempo_desarrollo, cierre, tiempo_cierre, trabajo_autonomo, fecha_entrega)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $id_unidad,
        $fecha_sabado,
        $contenido,
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

