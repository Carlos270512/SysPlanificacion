<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

// Verificar que el usuario sea admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

header('Content-Type: application/json');

$accion = $_POST['accion'] ?? '';

try {
    switch ($accion) {
        case 'crear':
            crearAsignatura($pdo);
            break;
        case 'obtener':
            obtenerAsignatura($pdo);
            break;
        case 'actualizar':
            actualizarAsignatura($pdo);
            break;
        case 'eliminar':
            eliminarAsignatura($pdo);
            break;
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function crearAsignatura($pdo) {
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre_asignatura = trim($_POST['nombre_asignatura'] ?? '');
    $horario = trim($_POST['horario'] ?? '') ?: null;
    $jornada = trim($_POST['jornada'] ?? '') ?: null;
    $periodo_academico = trim($_POST['periodo_academico'] ?? '');
    $aula = trim($_POST['aula'] ?? '') ?: null;
    $nivel = trim($_POST['nivel'] ?? '') ?: null;
    $fecha_inicio = trim($_POST['fecha_inicio'] ?? '') ?: null;
    $fecha_fin = trim($_POST['fecha_fin'] ?? '') ?: null;
    $docente_codigo = trim($_POST['docente_codigo'] ?? '') ?: null;

    // Validaciones
    if (empty($codigo)) {
        throw new Exception('El código es obligatorio');
    }
    if (empty($nombre_asignatura)) {
        throw new Exception('El nombre de la asignatura es obligatorio');
    }
    if (empty($periodo_academico)) {
        throw new Exception('El periodo lectivo es obligatorio');
    }

    // Verificar si el código ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM asignatura WHERE codigo = ?");
    $stmt->execute([$codigo]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe una asignatura con ese código');
    }

    // Insertar asignatura
    $stmt = $pdo->prepare("
        INSERT INTO asignatura (
            codigo, nombre_asignatura, horario, jornada, periodo_academico, 
            aula, nivel, fecha_inicio, fecha_fin, docente_codigo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $result = $stmt->execute([
        $codigo, $nombre_asignatura, $horario, $jornada, $periodo_academico,
        $aula, $nivel, $fecha_inicio, $fecha_fin, $docente_codigo
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Asignatura creada correctamente']);
    } else {
        throw new Exception('Error al crear la asignatura');
    }
}

function obtenerAsignatura($pdo) {
    $codigo = $_POST['codigo'] ?? '';
    
    if (empty($codigo)) {
        throw new Exception('Código no especificado');
    }

    $stmt = $pdo->prepare("
        SELECT codigo, nombre_asignatura, horario, jornada, periodo_academico, 
               aula, nivel, fecha_inicio, fecha_fin, docente_codigo 
        FROM asignatura 
        WHERE codigo = ?
    ");
    
    $stmt->execute([$codigo]);
    $asignatura = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($asignatura) {
        echo json_encode(['success' => true, 'data' => $asignatura]);
    } else {
        throw new Exception('Asignatura no encontrada');
    }
}

function actualizarAsignatura($pdo) {
    $codigo_original = trim($_POST['codigo_original'] ?? '');
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre_asignatura = trim($_POST['nombre_asignatura'] ?? '');
    $horario = trim($_POST['horario'] ?? '') ?: null;
    $jornada = trim($_POST['jornada'] ?? '') ?: null;
    $periodo_academico = trim($_POST['periodo_academico'] ?? '');
    $aula = trim($_POST['aula'] ?? '') ?: null;
    $nivel = trim($_POST['nivel'] ?? '') ?: null;
    $fecha_inicio = trim($_POST['fecha_inicio'] ?? '') ?: null;
    $fecha_fin = trim($_POST['fecha_fin'] ?? '') ?: null;
    $docente_codigo = trim($_POST['docente_codigo'] ?? '') ?: null;

    // Validaciones
    if (empty($codigo_original)) {
        throw new Exception('Código original no especificado');
    }
    if (empty($codigo)) {
        throw new Exception('El código es obligatorio');
    }
    if (empty($nombre_asignatura)) {
        throw new Exception('El nombre de la asignatura es obligatorio');
    }
    if (empty($periodo_academico)) {
        throw new Exception('El periodo lectivo es obligatorio');
    }

    // Si cambió el código, verificar que no exista otro con el nuevo código
    if ($codigo !== $codigo_original) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM asignatura WHERE codigo = ?");
        $stmt->execute([$codigo]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Ya existe una asignatura con ese código');
        }
    }

    // Actualizar asignatura
    $stmt = $pdo->prepare("
        UPDATE asignatura SET 
            codigo = ?, nombre_asignatura = ?, horario = ?, jornada = ?, 
            periodo_academico = ?, aula = ?, nivel = ?, fecha_inicio = ?, 
            fecha_fin = ?, docente_codigo = ?
        WHERE codigo = ?
    ");

    $result = $stmt->execute([
        $codigo, $nombre_asignatura, $horario, $jornada, $periodo_academico,
        $aula, $nivel, $fecha_inicio, $fecha_fin, $docente_codigo, $codigo_original
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Asignatura actualizada correctamente']);
    } else {
        throw new Exception('Error al actualizar la asignatura');
    }
}

function eliminarAsignatura($pdo) {
    $codigo = $_POST['codigo'] ?? '';
    
    if (empty($codigo)) {
        throw new Exception('Código no especificado');
    }

    // Verificar si la asignatura existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM asignatura WHERE codigo = ?");
    $stmt->execute([$codigo]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('Asignatura no encontrada');
    }

    // Eliminar asignatura
    $stmt = $pdo->prepare("DELETE FROM asignatura WHERE codigo = ?");
    $result = $stmt->execute([$codigo]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Asignatura eliminada correctamente']);
    } else {
        throw new Exception('Error al eliminar la asignatura');
    }
}
?>