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
if (!isset($_GET['fecha_documento']) || !isset($_GET['modalidad']) || 
    !isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin'])) {
    die('Error: Faltan parámetros necesarios para generar el PDF');
}

// Obtener parámetros
$fecha_documento = $_GET['fecha_documento'];
$modalidad = $_GET['modalidad'];
$fecha_inicio = $_GET['fecha_inicio'];
$fecha_fin = $_GET['fecha_fin'];

// Inicializar repository y obtener datos del coordinador
$repo = new CoevaluacionRepository($pdo);
$coordinadorLogueado = $repo->getDocentePorCodigo($_SESSION['usuario']['codigo']);

if (!$coordinadorLogueado) {
    die('Error: No se encontraron datos del coordinador');
}

// Función para convertir fecha a español
function fechaEspanol($fecha) {
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];
    
    $timestamp = strtotime($fecha);
    $dia = date('j', $timestamp);
    $mes = $meses[date('n', $timestamp)];
    $año = date('Y', $timestamp);
    
    return "$dia de $mes de $año";
}

// Convertir modalidad a texto legible
function modalidadTexto($modalidad) {
    switch($modalidad) {
        case 'presencial': return 'presencial';
        case 'en_linea': return 'en línea';
        case 'hibrida': return 'híbrida';
        case 'semi_presencial': return 'semi-presencial';
        default: return 'presencial';
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
    $html = generarHTMLPDF($coordinadorLogueado, $fecha_documento_formateada, 
                          $modalidad_texto, $fecha_inicio_formateada, $fecha_fin_formateada);

    $mpdf->WriteHTML($html);
    
    // Determinar si es descarga o visualización
    $output = isset($_GET['download']) && $_GET['download'] == '1' ? 'D' : 'I';
    $filename = 'coevaluacion_' . date('Y-m-d', strtotime($fecha_documento)) . '.pdf';
    
    $mpdf->Output($filename, $output);

} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}

function generarHTMLPDF($coordinador, $fecha_documento, $modalidad, $fecha_inicio, $fecha_fin) {
    // Ruta corregida para el logo
    $logoPath = __DIR__ . '/../assets/img/encabezadoPlani.png';
    $logoBase64 = '';
    
    // Convertir imagen a base64 para incluir en el PDF
    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
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
    </body>
    </html>
    ";
}
?>