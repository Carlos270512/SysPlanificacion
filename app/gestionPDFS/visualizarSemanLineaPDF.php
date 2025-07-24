<?php
// ...conexión y require de clases...
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/PlanificacionRepository.php';

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L', // Horizontal
    'margin_top' => 35, // Deja espacio para el encabezado
    'margin_header' => 5
]);

// Configurar el encabezado con la imagen del instituto
$imagePath = __DIR__ . '/../../public/assets/img/encabezadoPlani.png';
if (file_exists($imagePath)) {
    $imageData = base64_encode(file_get_contents($imagePath));
    $imageSrc = 'data:image/png;base64,' . $imageData;
} else {
    $imageSrc = '';
}

$header = '
<table width="100%" style="border-collapse: collapse;">
    <tr>
        <td width="100%" style="text-align: center;">
            <img src="' . $imageSrc . '" style="max-width: 100%; height: auto; max-height: 80px; width: 95%;">
        </td>
    </tr>
</table>';

// Establecer el encabezado para todas las páginas
$mpdf->SetHTMLHeader($header);

$id_semana_linea = isset($_GET['id_semana_linea']) ? intval($_GET['id_semana_linea']) : null;

$repo = new PlanificacionRepository($pdo);

$semana_linea = $id_semana_linea ? $repo->getSemanaLinea($id_semana_linea) : null;
$unidad = ($semana_linea && isset($semana_linea['id_unidad'])) ? $repo->getUnidad($semana_linea['id_unidad']) : null;
$asignatura = ($unidad && isset($unidad['asignatura_codigo'])) ? $repo->getAsignaturaConDocente($unidad['asignatura_codigo']) : null;

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
";

$html .= "
<br>
<table border='0' cellpadding='4' cellspacing='0' width='100%'>
    <tr>
        <td width='50%' style='font-size:16px;'><strong>Unidad N° " . htmlspecialchars($unidad['numero_unidad'] ?? '') . "</strong></td>
        <td width='50%' style='font-size:16px;'><strong>Nombre:</strong> " . htmlspecialchars($unidad['nombre'] ?? '') . "</td>
    </tr>
</table>

<table border='1' cellpadding='6' cellspacing='0' width='100%' style='font-size:10px;'>
    <tr style='background:#FFF9C4; text-align:center; font-weight:bold;'>
        <td width='35%'><strong>Objetivo de la unidad:</strong></td>
        <td width='25%'><strong>Equipo/Herramienta/ Recursos didácticos de la unidad:</strong></td>
        <td width='20%'><strong>Estrategia de enseñanza y aprendizaje</strong></td>
    </tr>
    <tr>
        <td valign='top'>" . ($unidad['objetivo_unidad'] ?? '') . "<br><br>
            <span style='font-weight:bold;'>Bibliografía:</span><br>
            " . ($unidad['bibliografia'] ?? '') . "
        </td>
        <td valign='top'>" . ($unidad['recursos_didacticos'] ?? '') . "</td>
        <td valign='top'>" . ($unidad['estrategia_ensenanza_aprendizaje'] ?? '') . "</td>
    </tr>
</table>
";

$mpdf->WriteHTML($html);

function fecha_es($fecha) {
    return $fecha ? date('d/m/Y', strtotime($fecha)) : '';
}

// AQUÍ CAMBIA: En lugar de mostrar solo una semana, mostrar todas las semanas de la unidad
if ($unidad && isset($unidad['id_unidad'])) {
    $semanas_linea = $repo->getSemanasLineaPorUnidad($unidad['id_unidad']);
    
    foreach ($semanas_linea as $semana_linea_item) {
        $mpdf->AddPage();
        
        $htmlSemanaLinea = "
        <br>
        <div style='font-size:13px; font-weight:bold; margin-bottom:4px;'>
            <span style='color:#222'>Sábado: " . fecha_es($semana_linea_item['fecha_sabado']) . "</span>
        </div>
        <table border='1' cellpadding='6' cellspacing='0' width='100%' style='font-size:11px;'>
            <tr style='background:#E0E0E0; text-align:center; font-weight:bold;'>
                <td width='18%'>Contenido</td>
                <td width='82%'>
                    Objetivo (s):<br>
                    <span style='font-weight:normal;'>" . $semana_linea_item['objetivo'] . "</span>
                    <br><br>
                    <span style='font-weight:bold;'>Actividades:</span>
                </td>
            </tr>
            <tr>
                <td valign='top' rowspan='5'>" . $semana_linea_item['contenido'] . "</td>
                <td>
                    <span style='font-weight:bold;'>Apertura:</span>
                    <span style='font-weight:normal;'>Tiempo: " . htmlspecialchars($semana_linea_item['tiempo_actividades']) . "</span>
                    <br>" . $semana_linea_item['actividades'] . "
                </td>
            </tr>
            <tr>
                <td>
                    <span style='font-weight:bold;'>Desarrollo:</span>
                    <span style='font-weight:normal;'>Tiempo: " . htmlspecialchars($semana_linea_item['tiempo_desarrollo']) . "</span>
                    <br>" . $semana_linea_item['desarrollo'] . "
                </td>
            </tr>
            <tr>
                <td>
                    <span style='font-weight:bold;'>Cierre:</span>
                    <span style='font-weight:normal;'>Tiempo: " . htmlspecialchars($semana_linea_item['tiempo_cierre']) . "</span>
                    <br>" . $semana_linea_item['cierre'] . "
                </td>
            </tr>
            <tr>
                <td>
                    <span style='font-weight:bold;'>Evaluación durante la clase:</span>
                    <br>" . $semana_linea_item['evaluacion_clase'] . "
                </td>
            </tr>
            <tr>
                <td>
                    <span style='font-weight:bold;'>Equipo/Herramienta/ Recursos didácticos / Recursos interactivos/ empleados en la clase:</span>
                    <br>" . $semana_linea_item['equipo_herramientas_recursos'] . "
                </td>
            </tr>
            <tr>
                <td colspan='2'>
                    <span style='font-weight:bold;'>Actividades de refuerzo (trabajo autónomo):</span>
                    <br>" . $semana_linea_item['actividades_refuerzo'] . "
                </td>
            </tr>
        </table>
        ";
        
        $mpdf->WriteHTML($htmlSemanaLinea);
    }
}

$mpdf->Output('planificacion_linea.pdf', 'I');