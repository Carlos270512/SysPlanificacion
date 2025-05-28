<?php
require_once __DIR__ . '/generarPdfController.php';
require_once __DIR__ . '/../../config/conexion.php';

// <-- Agrega esto
$semana_id = isset($_GET['semana_id']) ? intval($_GET['semana_id']) : 0;
$unidad_nombre = isset($_GET['unidad_nombre']) ? htmlspecialchars($_GET['unidad_nombre']) : '';
$semana_inicio = isset($_GET['semana_inicio']) ? htmlspecialchars($_GET['semana_inicio']) : '';
$semana_fin = isset($_GET['semana_fin']) ? htmlspecialchars($_GET['semana_fin']) : '';

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
?>
<div class="container mt-3">
    <h4>Datos completos para el PDF</h4>
    <table class="table table-bordered">
        <tr>
            <th>Asignatura:</th>
            <td><?= htmlspecialchars($asignatura['nombre_asignatura'] ?? '') ?></td>
            <th>Código de la asignatura:</th>
            <td><?= htmlspecialchars($asignatura['codigo'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Modalidad:</th>
            <td><?= htmlspecialchars($modalidad) ?></td>
            <th>Nivel:</th>
            <td><?= htmlspecialchars($asignatura['nivel'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Docente:</th>
            <td colspan="3"><?= htmlspecialchars($docente_nombre) ?></td>
        </tr>
        <tr>
            <th>Semana Inicio:</th>
            <td><?= htmlspecialchars($semana_inicio ?: ($unidad['semana_inicio'] ?? '')) ?></td>
            <th>Semana Fin:</th>
            <td><?= htmlspecialchars($semana_fin ?: ($unidad['semana_fin'] ?? '')) ?></td>
        </tr>
        <tr>
            <th>Nombre Unidad:</th>
            <td colspan="3"><?= htmlspecialchars($unidad ? $unidad['nombre'] : $unidad_nombre) ?></td>
        </tr>
        <tr>
            <th>Carrera Docente:</th>
            <td colspan="3"><?= htmlspecialchars($docente_carrera) ?></td>
        </tr>
        <tr>
            <th>ID Semana:</th>
            <td colspan="3"><?= htmlspecialchars($semana_id) ?></td>
        </tr>
    </table>
    <div class="alert alert-info mt-3">
        Aquí puedes mostrar el PDF o más información según lo que necesites.
    </div>
</div>
</div>

<!-- Mostrar el PDF generado en un iframe -->
<iframe 
    src="/SysPlanificacion/app/GestionPDF/verPdf.php?semana_id=<?= urlencode($semana_id) ?>" 
    width="100%" 
    height="600px" 
    style="border:1px solid #ccc; margin-top:20px;">
</iframe>