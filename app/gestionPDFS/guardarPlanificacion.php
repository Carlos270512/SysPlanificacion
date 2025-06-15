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
    $usuario_creacion = $docente ? $docente['codigo'] : null; // O usa $docente['nombre'] si prefieres

    $stmt = $pdo->prepare("INSERT INTO planificaciones (unidad_id, nombre_archivo, archivo_pdf, tipo_mime, usuario_creacion) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$unidad_id, $nombre_archivo, $archivo_pdf, $tipo_mime, $usuario_creacion]);

    echo json_encode(['success' => true, 'message' => 'PDF guardado correctamente.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}