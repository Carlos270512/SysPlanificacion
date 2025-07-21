<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    http_response_code(403);
    exit('Acceso denegado');
}

if (!isset($_POST['ids']) || !is_array($_POST['ids']) || empty($_POST['ids'])) {
    http_response_code(400);
    exit('No se seleccionaron planificaciones');
}

// Incluir conexión y repository
$pdo = require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/RepositoryPlanificacionesRepository.php';

$repository = new RepositoryPlanificacionesRepository($pdo);

// Validar que todos los IDs sean numéricos
$ids = array_filter($_POST['ids'], 'is_numeric');
if (empty($ids)) {
    http_response_code(400);
    exit('IDs inválidos');
}

// Crear archivo ZIP temporal
$zipFile = tempnam(sys_get_temp_dir(), 'planificaciones_') . '.zip';
$zip = new ZipArchive();

if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
    http_response_code(500);
    exit('Error al crear archivo ZIP');
}

$archivosAgregados = 0;

foreach ($ids as $id) {
    $planificacion = $repository->obtenerPlanificacionPorId($id);
    
    if ($planificacion && !empty($planificacion['archivo_pdf'])) {
        // Limpiar el nombre del archivo para evitar problemas
        $nombreArchivo = preg_replace('/[^a-zA-Z0-9._-]/', '_', $planificacion['nombre_archivo']);
        
        // Si no tiene extensión .pdf, agregarla
        if (!str_ends_with(strtolower($nombreArchivo), '.pdf')) {
            $nombreArchivo .= '.pdf';
        }
        
        // Agregar prefijo si hay duplicados
        $nombreFinal = $nombreArchivo;
        $contador = 1;
        while ($zip->locateName($nombreFinal) !== false) {
            $nombreSinExt = pathinfo($nombreArchivo, PATHINFO_FILENAME);
            $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
            $nombreFinal = $nombreSinExt . "_({$contador})." . $extension;
            $contador++;
        }
        
        $zip->addFromString($nombreFinal, $planificacion['archivo_pdf']);
        $archivosAgregados++;
    }
}

$zip->close();

if ($archivosAgregados === 0) {
    unlink($zipFile);
    http_response_code(404);
    exit('No se encontraron archivos PDF válidos');
}

// Preparar descarga
$nombreZip = 'Planificaciones_' . date('Y-m-d_H-i-s') . '.zip';

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $nombreZip . '"');
header('Content-Length: ' . filesize($zipFile));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Limpiar cualquier salida anterior
if (ob_get_level()) {
    ob_end_clean();
}

// Enviar archivo y limpiarlo después
readfile($zipFile);
unlink($zipFile);
exit();
?>