<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/PlanificacionRepository.php';

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L' // Horizontal
]);

$semana_id = $_POST['semana_id'] ?? null;
$unidad_id = $_POST['unidad_id'] ?? null;

$repo = new PlanificacionRepository($pdo);

$semana = $semana_id ? $repo->getSemana($semana_id) : null;
$unidad = $unidad_id ? $repo->getUnidad($unidad_id) : null;
$asignatura = ($unidad && isset($unidad['asignatura_codigo'])) ? $repo->getAsignaturaConDocente($unidad['asignatura_codigo']) : null;

// Campos que vienen de Quill.js (HTML)
$campos_html_unidad = [
    'objetivo_unidad',
    'metodologia',
    'actividades_recuperacion',
    'recursos_didacticos',
    'bibliografia'
];

// --- Encabezado y tabla principal ---
$html = "
<div style='text-align:center; font-size:22px; font-weight:bold;'>Sede Matriz</div>
<div style='text-align:center; font-size:18px; font-weight:bold; margin-bottom:10px;'>PLANIFICACIÓN DE CLASE</div>
<table border='1' cellpadding='4' cellspacing='0' width='100%'>
    <tr>
        <td width='20%'><strong>Asignatura:</strong></td>
        <td width='30%'>" . htmlspecialchars($asignatura['nombre_asignatura'] ?? '') . "</td>
        <td width='20%'><strong>Código de la asignatura:</strong></td>
        <td width='30%'>" . htmlspecialchars($asignatura['codigo'] ?? '') . "</td>
    </tr>
    <tr>
        <td><strong>Modalidad:</strong></td>
        <td>" . htmlspecialchars($asignatura['jornada'] ?? '') . "</td>
        <td><strong>Nivel:</strong></td>
        <td>" . htmlspecialchars($asignatura['nivel'] ?? '') . "</td>
    </tr>
    <tr>
        <td><strong>Docente:</strong></td>
        <td colspan='3'>" . htmlspecialchars($asignatura['docente_nombre'] ?? '') . "</td>
    </tr>
</table>
<br>
<table border='0' cellpadding='4' cellspacing='0' width='100%'>
    <tr>
        <td width='50%' style='font-size:16px;'><strong>Unidad N° " . htmlspecialchars($unidad['numero_unidad'] ?? '') . "</strong></td>
        <td width='50%' style='font-size:16px;'><strong>Nombre:</strong> " . htmlspecialchars($unidad['nombre'] ?? '') . "</td>
    </tr>
</table>
";

// --- Tabla de detalles de la unidad ---
$html .= "
<table border='1' cellpadding='6' cellspacing='0' width='100%'>
    <tr style='background:#FFF9C4; text-align:center; font-weight:bold;'>
        <td width='25%'><strong>Objetivo de la unidad:</strong></td>
        <td width='25%'><strong>Metodologías de evaluación de la unidad:</strong></td>
        <td width='25%'><strong>Actividades de recuperación de la unidad:</strong></td>
        <td width='25%'><strong>Equipo/Herramienta/Recursos didácticos de la unidad:</strong></td>
    </tr>
    <tr>
        <td valign='top'>" . ($unidad ? $unidad['objetivo_unidad'] : '') . "</td>
        <td valign='top'>" . ($unidad ? $unidad['metodologia'] : '') . "</td>
        <td valign='top'>" . ($unidad ? $unidad['actividades_recuperacion'] : '') . "</td>
        <td valign='top'>" . ($unidad ? $unidad['recursos_didacticos'] : '') . "</td>
    </tr>
    <tr>
        <td colspan='4'>
            <span style='font-weight:bold;'>Bibliografía:</span><br>
            " . ($unidad ? $unidad['bibliografia'] : '') . "
        </td>
    </tr>
</table>
";

$mpdf->WriteHTML($html);
// --- NUEVA HOJA: PLANIFICACIÓN SEMANAL ---
if ($semana) {
    $mpdf->AddPage();

    function fecha_es($fecha) {
        return $fecha ? date('d-m-Y', strtotime($fecha)) : '';
    }

    $htmlSemana = "
    <div style='font-size:14px; font-weight:bold; margin-bottom:4px;'>Semana: Del " . fecha_es($semana['fecha_semana']) . "</div>
    <div style='font-size:12px'>
    <table border='1' cellpadding='4' cellspacing='0' width='100%'>
        <tr style='background:#FFF9C4; font-weight:bold;'>
            <td colspan='6'>Actividades previas a la clase</td>
        </tr>
        <tr>
            <td colspan='6'>" . $semana['actividades_previas'] . "</td>
        </tr>
        <tr style='background:#FFF9C4; font-weight:bold;'>
            <td colspan='6'>Contenido:</td>
        </tr>
        <tr>
            <td colspan='6'>" . $semana['contenido'] . "</td>
        </tr>
    </table>
    <br>
    <table border='1' cellpadding='4' cellspacing='0' width='100%'>
        <tr style='background:#FFF9C4; text-align:center; font-weight:bold; font-size:12px;'>
            <td width='16%'></td>
            <td width='16%'>Lunes</td>
            <td width='16%'>Martes</td>
            <td width='16%'>Miércoles</td>
            <td width='16%'>Jueves</td>
            <td width='16%'>Viernes</td>
        </tr>
        <tr>
            <td style='font-weight:bold;'>Objetivo</td>
            <td>" . $semana['objetivo_lunes'] . "</td>
            <td>" . $semana['objetivo_martes'] . "</td>
            <td>" . $semana['objetivo_miercoles'] . "</td>
            <td>" . $semana['objetivo_jueves'] . "</td>
            <td>" . $semana['objetivo_viernes'] . "</td>
        </tr>
        <tr>
            <td style='font-weight:bold;'>Apertura</td>
            <td>" . $semana['apertura_lunes'] . "<br><small>Tiempo: " . $semana['tiempo_apertura_lunes'] . "</small></td>
            <td>" . $semana['apertura_martes'] . "<br><small>Tiempo: " . $semana['tiempo_apertura_martes'] . "</small></td>
            <td>" . $semana['apertura_miercoles'] . "<br><small>Tiempo: " . $semana['tiempo_apertura_miercoles'] . "</small></td>
            <td>" . $semana['apertura_jueves'] . "<br><small>Tiempo: " . $semana['tiempo_apertura_jueves'] . "</small></td>
            <td>" . $semana['apertura_viernes'] . "<br><small>Tiempo: " . $semana['tiempo_apertura_viernes'] . "</small></td>
        </tr>
        <tr>
            <td style='font-weight:bold;'>Desarrollo</td>
            <td>" . $semana['desarrollo_lunes'] . "<br><small>Tiempo: " . $semana['tiempo_desarrollo_lunes'] . "</small></td>
            <td>" . $semana['desarrollo_martes'] . "<br><small>Tiempo: " . $semana['tiempo_desarrollo_martes'] . "</small></td>
            <td>" . $semana['desarrollo_miercoles'] . "<br><small>Tiempo: " . $semana['tiempo_desarrollo_miercoles'] . "</small></td>
            <td>" . $semana['desarrollo_jueves'] . "<br><small>Tiempo: " . $semana['tiempo_desarrollo_jueves'] . "</small></td>
            <td>" . $semana['desarrollo_viernes'] . "<br><small>Tiempo: " . $semana['tiempo_desarrollo_viernes'] . "</small></td>
        </tr>
        <tr>
            <td style='font-weight:bold;'>Cierre</td>
            <td>" . $semana['cierre_lunes'] . "<br><small>Tiempo: " . $semana['tiempo_cierre_lunes'] . "</small></td>
            <td>" . $semana['cierre_martes'] . "<br><small>Tiempo: " . $semana['tiempo_cierre_martes'] . "</small></td>
            <td>" . $semana['cierre_miercoles'] . "<br><small>Tiempo: " . $semana['tiempo_cierre_miercoles'] . "</small></td>
            <td>" . $semana['cierre_jueves'] . "<br><small>Tiempo: " . $semana['tiempo_cierre_jueves'] . "</small></td>
            <td>" . $semana['cierre_viernes'] . "<br><small>Tiempo: " . $semana['tiempo_cierre_viernes'] . "</small></td>
        </tr>
        <tr>
            <td style='font-weight:bold;'>Trabajo autónomo</td>
            <td>" . $semana['trabajo_autonomo_lunes'] . "<br><small>Fecha entrega: " . fecha_es($semana['fecha_entrega_lunes']) . "</small></td>
            <td>" . $semana['trabajo_autonomo_martes'] . "<br><small>Fecha entrega: " . fecha_es($semana['fecha_entrega_martes']) . "</small></td>
            <td>" . $semana['trabajo_autonomo_miercoles'] . "<br><small>Fecha entrega: " . fecha_es($semana['fecha_entrega_miercoles']) . "</small></td>
            <td>" . $semana['trabajo_autonomo_jueves'] . "<br><small>Fecha entrega: " . fecha_es($semana['fecha_entrega_jueves']) . "</small></td>
            <td>" . $semana['trabajo_autonomo_viernes'] . "<br><small>Fecha entrega: " . fecha_es($semana['fecha_entrega_viernes']) . "</small></td>
        </tr>
    </table>
    </div>
    ";
    $mpdf->WriteHTML($htmlSemana);
}

$mpdf->Output('planificacion.pdf', 'I');
$mpdf->Output('planificacion.pdf', 'I');
