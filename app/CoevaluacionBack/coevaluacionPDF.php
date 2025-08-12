<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'COORDINADOR') {
    header("Location: ../index.php");
    exit();
}

// Conexión a la base de datos
$pdo = require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../app/CoevaluacionBack/coevaluacion_Repository.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Mpdf\Mpdf;

// Validar que se recibieron los parámetros
if (
    !isset($_GET['fecha_documento']) || !isset($_GET['modalidad']) ||
    !isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin'])
) {
    die('Error: Faltan parámetros necesarios para generar el PDF');
}

// Obtener parámetros
$fecha_documento = $_GET['fecha_documento'];
$modalidad = $_GET['modalidad'];
$fecha_inicio = $_GET['fecha_inicio'];
$fecha_fin = $_GET['fecha_fin'];
$coevaluaciones = isset($_GET['coevaluaciones']) ? json_decode($_GET['coevaluaciones'], true) : [];

// Inicializar repository y obtener datos del coordinador
$repo = new CoevaluacionRepository($pdo);
$coordinadorLogueado = $repo->getDocentePorCodigo($_SESSION['usuario']['codigo']);

if (!$coordinadorLogueado) {
    die('Error: No se encontraron datos del coordinador');
}

// Función para convertir fecha a español
function fechaEspanol($fecha)
{
    $meses = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    ];

    $timestamp = strtotime($fecha);
    $dia = date('j', $timestamp);
    $mes = $meses[date('n', $timestamp)];
    $año = date('Y', $timestamp);

    return "$dia de $mes de $año";
}

// Convertir modalidad a texto legible
function modalidadTexto($modalidad)
{
    switch ($modalidad) {
        case 'presencial':
            return 'presencial';
        case 'en_linea':
            return 'en línea';
        case 'hibrida':
            return 'híbrida';
        case 'semi_presencial':
            return 'semi-presencial';
        default:
            return 'presencial';
    }
}

// Formatear fechas
$fecha_documento_formateada = fechaEspanol($fecha_documento);
$fecha_inicio_formateada = fechaEspanol($fecha_inicio);
$fecha_fin_formateada = fechaEspanol($fecha_fin);
$modalidad_texto = modalidadTexto($modalidad);

// Crear el PDF
try {
    $mpdf = new Mpdf([
        'format' => 'A4',
        'margin_top' => 15,
        'margin_bottom' => 15,
        'margin_left' => 20,
        'margin_right' => 20,
        'default_font' => 'Arial'
    ]);

    // HTML del PDF
    $html = generarHTMLPDF(
        $coordinadorLogueado,
        $fecha_documento_formateada,
        $modalidad_texto,
        $fecha_inicio_formateada,
        $fecha_fin_formateada,
        $coevaluaciones
    );

    $mpdf->WriteHTML($html);

    // Determinar si es descarga o visualización
    $output = isset($_GET['download']) && $_GET['download'] == '1' ? 'D' : 'I';
    $filename = 'coevaluacion_' . date('Y-m-d', strtotime($fecha_documento)) . '.pdf';

    $mpdf->Output($filename, $output);
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}

function generarHTMLPDF($coordinador, $fecha_documento, $modalidad, $fecha_inicio, $fecha_fin, $coevaluaciones)
{
    // Ruta corregida para el logo
    $logoPath = __DIR__ . '/../../public/assets/img/encabezadoPlani.png';
    $logoBase64 = '';

    // Convertir imagen a base64 para incluir en el PDF
    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }

    // Generar tabla de coevaluaciones si existen
    $tablaHTML = '';
    if (!empty($coevaluaciones)) {
        $tablaHTML = "
        <div style='margin-top: 30px;'>
            <table style='width: 100%; border-collapse: collapse; font-size: 9px;'>
                <thead>
                    <tr style='background-color: #4a90e2; color: white;'>
                        <th style='border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;'>CARRERA</th>
                        <th style='border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;'>MODALIDAD</th>
                        <th style='border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;'>DOCENTE (CÓDIGO)</th>
                        <th style='border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;'>FECHA</th>
                        <th style='border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;'>HORA</th>
                        <th style='border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;'>ASIGNATURA (CÓDIGO)</th>
                        <th style='border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;'>OBSERVACIONES</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($coevaluaciones as $index => $coevaluacion) {
            // Alternar colores de filas
            $backgroundColor = ($index % 2 == 0) ? '#f8f9fa' : '#ffffff';

            // Formatear fecha
            $fechaFormateada = date('d/m/Y', strtotime($coevaluacion['fecha']));

            $tablaHTML .= "
                    <tr style='background-color: {$backgroundColor};'>
                        <td style='border: 1px solid #000; padding: 6px; text-align: center; font-size: 8px;'>{$coevaluacion['carrera']}</td>
                        <td style='border: 1px solid #000; padding: 6px; text-align: center; font-size: 8px;'>{$coevaluacion['modalidad']}</td>
                        <td style='border: 1px solid #000; padding: 6px; text-align: center; font-size: 8px;'>{$coevaluacion['docente_nombre']}</td>
                        <td style='border: 1px solid #000; padding: 6px; text-align: center; font-size: 8px;'>{$fechaFormateada}</td>
                        <td style='border: 1px solid #000; padding: 6px; text-align: center; font-size: 8px;'>{$coevaluacion['hora']}</td>
                        <td style='border: 1px solid #000; padding: 6px; text-align: center; font-size: 8px;'>{$coevaluacion['asignatura_nombre']}</td>
                        <td style='border: 1px solid #000; padding: 6px; text-align: center; font-size: 8px;'>{$coevaluacion['observaciones']}</td>
                    </tr>";
        }

        $tablaHTML .= "
                </tbody>
            </table>
        </div>";
    }

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
                margin: 0;
                padding: 0;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .header img {
                width: 100%;
                max-width: 700px;
                height: auto;
            }
            .fecha-documento {
                text-align: right;
                margin-bottom: 30px;
                font-size: 12px;
            }
            .seccion {
                margin-bottom: 20px;
                font-size: 12px;
            }
            .seccion-titulo {
                font-weight: bold;
                margin-bottom: 5px;
            }
            .contenido {
                text-align: justify;
                line-height: 1.6;
                margin-bottom: 20px;
            }
            .destacado {
                font-weight: bold;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
             .firmas-container {
                margin-top: 50px;
                width: 100%;
                display: block;
            }
            .firma-box {
                width: 49%;
                display: inline-block;
                vertical-align: top;
                padding: 15px;
                min-height: 120px;
                box-sizing: border-box;
            }
            .firma-box:first-child {
                margin-right: 1%;
            }
            .firma-titulo {
                font-weight: bold;
                font-size: 12px;
                margin-bottom: 50px;
            }
            .firma-linea {
                border-bottom: 1px solid #000;
                width: 100%;
                margin-bottom: 8px;
                height: 1px;
            }
            .firma-nombre {
                font-size: 10px;
                line-height: 1.2;
                text-align: left;
            }
            .firma-nombre {
                font-size: 10px;
                line-height: 1.2;
                text-align: left;
            }
        </style>
    </head>
    <body>
        <!-- Encabezado con logo -->
        <div class='header'>
            " . ($logoBase64 ? "<img src='$logoBase64' alt='Encabezado'>" : '') . "
        </div>
        
        <!-- Fecha del documento -->
        <div class='fecha-documento'>
            Quito, {$fecha_documento}
        </div>
        
        <!-- DE: -->
        <div class='seccion'>
            <div class='seccion-titulo'>DE:</div>
            <div>{$coordinador['titulo_abreviado']}</div>
            <div>{$coordinador['nombre']}</div>
            <div>Coordinador de Carrera de {$coordinador['carrera']}</div>
        </div>
        
        <!-- PARA: -->
        <div class='seccion'>
            <div class='seccion-titulo'>PARA:</div>
            <div>PhD.</div>
            <div>Mario Guamán</div>
            <div>Coordinador Académico (Sede Matriz)</div>
        </div>
        
        <!-- Contenido principal -->
        <div class='contenido'>
            <div class='destacado'>De mis consideraciones:</div>
            <br><br>
            Por medio de la presente aprovecho para saludarle atentamente y a la vez desearle éxitos 
            en las funciones que muy acertadamente desempeña. Cumpliendo con las obligaciones y 
            responsabilidades como coordinador de la carrera de <span class='destacado'>{$coordinador['carrera']}</span> 
            modalidad <span class='destacado'>{$modalidad}</span> pongo en su conocimiento las coevaluaciones que se 
            realizarán desde el <span class='destacado'>{$fecha_inicio}</span> al <span class='destacado'>{$fecha_fin}</span>.
        </div>
        
        <!-- Tabla de coevaluaciones -->
        {$tablaHTML}
        
        <!-- Texto adicional -->
        <div style='margin-top: 30px; font-size: 12px;'>
            Por la atención que se digne dar a la presente, anticipo mis más sinceros agradecimientos.
        </div>
        
        <!-- Sección de firmas -->
        <div class='firmas-container'>
            <div class='firma-box'>
                <div class='firma-titulo'>Elaborado:</div>
                <div class='firma-linea'></div>
                <div class='firma-nombre'>
                    {$coordinador['titulo_abreviado']} {$coordinador['nombre']}<br>
                    Coordinador de Carrera de {$coordinador['carrera']}
                </div>
            </div>
            
            <div class='firma-box'>
                <div class='firma-titulo'>Aprobado:</div>
                <div class='firma-linea'></div>
                <div class='firma-nombre'>
                    Ing. Mario Guamán, PhD.<br>
                    Coordinador Académico (Sede Matriz)<br>
                    <strong>Observaciones:</strong>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
}
