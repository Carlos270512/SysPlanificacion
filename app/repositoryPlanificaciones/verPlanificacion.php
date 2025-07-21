<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    http_response_code(403);
    exit('Acceso denegado');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit('ID inválido');
}

// Incluir conexión y repository
$pdo = require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/RepositoryPlanificacionesRepository.php';

$repository = new RepositoryPlanificacionesRepository($pdo);
$planificacion = $repository->obtenerPlanificacionPorId($_GET['id']);

if (!$planificacion) {
    http_response_code(404);
    exit('Planificación no encontrada');
}

if (empty($planificacion['archivo_pdf'])) {
    http_response_code(404);
    exit('Archivo PDF no disponible');
}

// Establecer headers para mostrar PDF inline
header('Content-Type: ' . ($planificacion['tipo_mime'] ?? 'application/pdf'));
header('Content-Disposition: inline; filename="' . $planificacion['nombre_archivo'] . '"');
header('Content-Length: ' . strlen($planificacion['archivo_pdf']));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Limpiar cualquier salida anterior
if (ob_get_level()) {
    ob_end_clean();
}

// Enviar el contenido del PDF
echo $planificacion['archivo_pdf'];
exit();
?>