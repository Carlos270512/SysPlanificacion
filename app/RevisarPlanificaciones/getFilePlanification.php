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

header('Content-Type: ' . $pdf['tipo_mime']);
header('Content-Disposition: inline; filename="' . $pdf['nombre_archivo'] . '"');
echo $pdf['archivo_pdf'];
exit;