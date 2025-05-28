<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/generarPdfController.php';
require_once __DIR__ . '/../../config/conexion.php';

$semana_id = isset($_GET['semana_id']) ? intval($_GET['semana_id']) : 0;

$controller = new GenerarPdfController($pdo);
$datos = $controller->obtenerDatosPorSemana($semana_id);

$semana = isset($datos['semana']) ? $datos['semana'] : null;
$unidad = isset($datos['unidad']) ? $datos['unidad'] : null;
$asignatura = isset($datos['asignatura']) ? $datos['asignatura'] : null;
$docente = isset($datos['docente']) ? $datos['docente'] : null;
$docente_nombre = $docente['nombre'] ?? '';
$docente_carrera = $docente['carrera'] ?? '';
$modalidad = ($asignatura && isset($asignatura['jornada']))
    ? GenerarPdfController::obtenerModalidadPorJornada(trim($asignatura['jornada']))
    : '';
$unidad_nombre = $unidad['nombre'] ?? '';
$semana_inicio = $unidad['semana_inicio'] ?? '';
$semana_fin = $unidad['semana_fin'] ?? '';
$nivel = $asignatura['nivel'] ?? '';
$codigo_asignatura = $asignatura['codigo'] ?? '';
$nombre_asignatura = $asignatura['nombre_asignatura'] ?? '';

$html = '
<style>
    body { font-family: Arial, sans-serif; }
    .container { margin-top: 20px; }
    .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .table th, .table td { border: 1px solid #ccc; padding: 8px; }
    .table th { background: #f5f5f5; text-align: left; }
    h4 { margin-bottom: 16px; }
</style>
<div class="container">
    <h4>Datos completos para el PDF</h4>
    <table class="table">
        <tr>
            <th>Asignatura:</th>
            <td>' . htmlspecialchars($nombre_asignatura) . '</td>
            <th>Código de la asignatura:</th>
            <td>' . htmlspecialchars($codigo_asignatura) . '</td>
        </tr>
        <tr>
            <th>Modalidad:</th>
            <td>' . htmlspecialchars($modalidad) . '</td>
            <th>Nivel:</th>
            <td>' . htmlspecialchars($nivel) . '</td>
        </tr>
        <tr>
            <th>Docente:</th>
            <td colspan="3">' . htmlspecialchars($docente_nombre) . '</td>
        </tr>
        <tr>
            <th>Semana Inicio:</th>
            <td>' . htmlspecialchars($semana_inicio) . '</td>
            <th>Semana Fin:</th>
            <td>' . htmlspecialchars($semana_fin) . '</td>
        </tr>
        <tr>
            <th>Nombre Unidad:</th>
            <td colspan="3">' . htmlspecialchars($unidad_nombre) . '</td>
        </tr>
        <tr>
            <th>Carrera Docente:</th>
            <td colspan="3">' . htmlspecialchars($docente_carrera) . '</td>
        </tr>
        <tr>
            <th>ID Semana:</th>
            <td colspan="3">' . htmlspecialchars($semana_id) . '</td>
        </tr>
    </table>
</div>
';

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output('reporte.pdf', \Mpdf\Output\Destination::INLINE);