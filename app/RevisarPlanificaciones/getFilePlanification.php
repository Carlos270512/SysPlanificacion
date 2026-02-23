<?php
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_GET['unidad_id'])) {
    http_response_code(400);
    exit('Unidad no especificada');
}

$unidad_id = intval($_GET['unidad_id']);
$stmt = $pdo->prepare("SELECT archivo_pdf, tipo_mime, nombre_archivo FROM planificaciones WHERE unidad_id = ? LIMIT 1");
$stmt->execute([$unidad_id]);
$pdf = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pdf) {
    http_response_code(404);
    exit('Archivo no encontrado');
}

// Limpiar cualquier salida previa
if (ob_get_level()) {
    ob_end_clean();
}

// Headers necesarios para Chrome
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $pdf['nombre_archivo'] . '"');
header('Content-Length: ' . strlen($pdf['archivo_pdf']));
header('Accept-Ranges: bytes');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');

// Enviar el PDF
echo $pdf['archivo_pdf'];
flush();
exit;