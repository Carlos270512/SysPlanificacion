<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/PlanificacionRepository.php';

header('Content-Type: application/json');

try {
    if (
        !isset($_POST['unidad_id']) ||
        !isset($_POST['nombre_archivo']) ||
        !isset($_FILES['archivo_pdf'])
    ) {
        throw new Exception('Faltan datos requeridos.');
    }

    $unidad_id = intval($_POST['unidad_id']);
    $nombre_archivo = trim($_POST['nombre_archivo']);
    $tipo_mime = $_FILES['archivo_pdf']['type'] ?? 'application/pdf';
    $archivo_pdf = file_get_contents($_FILES['archivo_pdf']['tmp_name']);

    // Obtener el usuario (docente) asociado a la unidad usando el repositorio
    $repo = new PlanificacionRepository($pdo);
    $docente = $repo->getDocentePorUnidad($unidad_id);
    $usuario = $docente ? $docente['codigo'] : null; // O usa $docente['nombre'] si prefieres

    // Verificar si ya existe una planificación para esa unidad
    $stmt = $pdo->prepare("SELECT id_planificacion FROM planificaciones WHERE unidad_id = ?");
    $stmt->execute([$unidad_id]);
    $existe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        // Actualizar PDF existente
        $stmt = $pdo->prepare("UPDATE planificaciones 
            SET nombre_archivo = ?, archivo_pdf = ?, tipo_mime = ?, fecha_actualizacion = NOW(), usuario_actualizacion = ?
            WHERE unidad_id = ?");
        $stmt->execute([$nombre_archivo, $archivo_pdf, $tipo_mime, $usuario, $unidad_id]);
        echo json_encode(['success' => true, 'message' => 'PDF actualizado correctamente.']);
    } else {
        // Insertar nuevo PDF
        $stmt = $pdo->prepare("INSERT INTO planificaciones (unidad_id, nombre_archivo, archivo_pdf, tipo_mime, usuario_creacion) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$unidad_id, $nombre_archivo, $archivo_pdf, $tipo_mime, $usuario]);
        echo json_encode(['success' => true, 'message' => 'PDF guardado correctamente.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}