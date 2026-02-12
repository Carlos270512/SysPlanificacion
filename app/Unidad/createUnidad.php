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

// Determinar si es unidad base (automático si numero_unidad = 1)
$unidad_base = ($numero_unidad == 1) ? 1 : 0;

// Si NO es la unidad base, buscar la unidad base de esta asignatura y copiar sus campos
if ($numero_unidad != 1) {
    try {
        $stmtBase = $pdo->prepare("SELECT objetivo_unidad, metodologia, actividades_recuperacion, 
                                    recursos_didacticos, bibliografia, estrategia_ensenanza_aprendizaje 
                                    FROM unidad 
                                    WHERE asignatura_codigo = ? 
                                    AND numero_unidad = 1 
                                    AND unidad_base = 1 
                                    LIMIT 1");
        $stmtBase->execute([$asignatura_codigo]);
        $unidadBase = $stmtBase->fetch(PDO::FETCH_ASSOC);
        
        // Si existe unidad base, copiar sus campos solo si no vienen del formulario
        if ($unidadBase) {
            if (empty($objetivo_unidad)) {
                $objetivo_unidad = $unidadBase['objetivo_unidad'];
            }
            if (empty($metodologia)) {
                $metodologia = $unidadBase['metodologia'];
            }
            if (empty($actividades_recuperacion)) {
                $actividades_recuperacion = $unidadBase['actividades_recuperacion'];
            }
            if (empty($recursos_didacticos)) {
                $recursos_didacticos = $unidadBase['recursos_didacticos'];
            }
            if (empty($bibliografia)) {
                $bibliografia = $unidadBase['bibliografia'];
            }
            if (empty($estrategia)) {
                $estrategia = $unidadBase['estrategia_ensenanza_aprendizaje'];
            }
        }
    } catch (Exception $e) {
        // Si hay error al buscar la unidad base, continuar sin copiar
        error_log("Error al buscar unidad base: " . $e->getMessage());
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO unidad 
        (asignatura_codigo, numero_unidad, nombre, objetivo_unidad, bibliografia, metodologia, actividades_recuperacion, recursos_didacticos, estrategia_ensenanza_aprendizaje, semana_inicio, semana_fin, unidad_base) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
        $semana_fin,
        $unidad_base
    ]);
    $unidad_id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'unidad_id' => $unidad_id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
}