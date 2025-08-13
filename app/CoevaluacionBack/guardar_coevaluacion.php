<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
use Mpdf\Mpdf;

// Verificar que el usuario esté logueado como coordinador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'COORDINADOR') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit();
}

// Verificar que es una petición POST para guardar
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['guardar_pdf'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

try {
    // Conexión a la base de datos
    $pdo = require_once __DIR__ . '/../../config/conexion.php';
    require_once __DIR__ . '/coevaluacion_Repository.php';
    
    // Inicializar repository
    $repo = new CoevaluacionRepository($pdo);
    
    // Obtener datos del formulario
    $fecha_documento = $_POST['fecha_documento'] ?? '';
    $modalidad = $_POST['modalidad'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $coevaluaciones = json_decode($_POST['coevaluaciones'] ?? '[]', true);
    
    // Validaciones básicas
    if (empty($fecha_documento) || empty($modalidad) || empty($fecha_inicio) || empty($fecha_fin)) {
        throw new Exception('Faltan datos obligatorios del formulario');
    }
    
    if (empty($coevaluaciones)) {
        throw new Exception('No hay coevaluaciones para guardar');
    }
    
    // Obtener datos del coordinador
    $coordinador = $repo->getDocentePorCodigo($_SESSION['usuario']['codigo']);
    if (!$coordinador) {
        throw new Exception('Error: No se encontraron datos del coordinador');
    }
    
    // Función para convertir fecha a español
    function fechaEspanolDirecto($fecha) {
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
    
    // Función para modalidad texto
    function modalidadTextoDirecto($modalidad) {
        switch ($modalidad) {
            case 'presencial': return 'presencial';
            case 'en_linea': return 'en línea';
            case 'hibrida': return 'híbrida';
            case 'semi_presencial': return 'semi-presencial';
            default: return 'presencial';
        }
    }
    
    // Formatear fechas
    $fecha_documento_formateada = fechaEspanolDirecto($fecha_documento);
    $fecha_inicio_formateada = fechaEspanolDirecto($fecha_inicio);
    $fecha_fin_formateada = fechaEspanolDirecto($fecha_fin);
    $modalidad_texto = modalidadTextoDirecto($modalidad);
    
    // Ruta para el logo
    $logoPath = __DIR__ . '/../../public/assets/img/encabezadoPlani.png';
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }
    
    // Generar tabla de coevaluaciones
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
            $backgroundColor = ($index % 2 == 0) ? '#f8f9fa' : '#ffffff';
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
        $tablaHTML .= "</tbody></table></div>";
    }
    
    // HTML completo del PDF
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; margin: 0; padding: 0; }
            .header { text-align: center; margin-bottom: 30px; }
            .header img { width: 100%; max-width: 700px; height: auto; }
            .fecha-documento { text-align: right; margin-bottom: 30px; font-size: 12px; }
            .seccion { margin-bottom: 20px; font-size: 12px; }
            .seccion-titulo { font-weight: bold; margin-bottom: 5px; }
            .contenido { text-align: justify; line-height: 1.6; margin-bottom: 20px; }
            .destacado { font-weight: bold; }
            .firmas-container { margin-top: 50px; width: 100%; display: block; }
            .firma-box { width: 49%; display: inline-block; vertical-align: top; padding: 15px; min-height: 120px; box-sizing: border-box; }
            .firma-box:first-child { margin-right: 1%; }
            .firma-titulo { font-weight: bold; font-size: 12px; margin-bottom: 50px; }
            .firma-linea { border-bottom: 1px solid #000; width: 100%; margin-bottom: 8px; height: 1px; }
            .firma-nombre { font-size: 10px; line-height: 1.2; text-align: left; }
        </style>
    </head>
    <body>
        <div class='header'>" . ($logoBase64 ? "<img src='$logoBase64' alt='Encabezado'>" : '') . "</div>
        <div class='fecha-documento'>Quito, {$fecha_documento_formateada}</div>
        <div class='seccion'>
            <div class='seccion-titulo'>DE:</div>
            <div>{$coordinador['titulo_abreviado']}</div>
            <div>{$coordinador['nombre']}</div>
            <div>Coordinador de Carrera de {$coordinador['carrera']}</div>
        </div>
        <div class='seccion'>
            <div class='seccion-titulo'>PARA:</div>
            <div>PhD.</div>
            <div>Mario Guamán</div>
            <div>Coordinador Académico (Sede Matriz)</div>
        </div>
        <div class='contenido'>
            <div class='destacado'>De mis consideraciones:</div>
            <br><br>
            Por medio de la presente aprovecho para saludarle atentamente y a la vez desearle éxitos 
            en las funciones que muy acertadamente desempeña. Cumpliendo con las obligaciones y 
            responsabilidades como coordinador de la carrera de <span class='destacado'>{$coordinador['carrera']}</span> 
            modalidad <span class='destacado'>{$modalidad_texto}</span> pongo en su conocimiento las coevaluaciones que se 
            realizarán desde el <span class='destacado'>{$fecha_inicio_formateada}</span> al <span class='destacado'>{$fecha_fin_formateada}</span>.
        </div>
        {$tablaHTML}
        <div style='margin-top: 30px; font-size: 12px;'>
            Por la atención que se digne dar a la presente, anticipo mis más sinceros agradecimientos.
        </div>
        <div class='firmas-container'>
            <div class='firma-box'>
                <div class='firma-titulo'>Elaborado:</div>
                <div class='firma-linea'></div>
                <div class='firma-nombre'>{$coordinador['titulo_abreviado']} {$coordinador['nombre']}<br>Coordinador de Carrera de {$coordinador['carrera']}</div>
            </div>
            <div class='firma-box'>
                <div class='firma-titulo'>Aprobado:</div>
                <div class='firma-linea'></div>
                <div class='firma-nombre'>Ing. Mario Guamán, PhD.<br>Coordinador Académico (Sede Matriz)<br><strong>Observaciones:</strong></div>
            </div>
        </div>
    </body>
    </html>";
    
    // Crear PDF
    $mpdf = new Mpdf([
        'format' => 'A4',
        'margin_top' => 15,
        'margin_bottom' => 15,
        'margin_left' => 20,
        'margin_right' => 20,
        'default_font' => 'Arial'
    ]);
    
    $mpdf->WriteHTML($html);
    $contenidoPDF = $mpdf->Output('', 'S');
    
    // Obtener período académico
    $primeraAsignatura = !empty($coevaluaciones) ? $coevaluaciones[0]['asignatura_codigo'] : '';
    $periodoLectivo = 'SIN_PERIODO';
    $asignaturaCompleta = '';
    
    if ($primeraAsignatura) {
        $stmt = $pdo->prepare("SELECT periodo_academico, nombre_asignatura FROM asignatura WHERE codigo = ?");
        $stmt->execute([$primeraAsignatura]);
        $asignaturaData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($asignaturaData) {
            $periodoLectivo = $asignaturaData['periodo_academico'] ?: 'SIN_PERIODO';
            $asignaturaCompleta = $primeraAsignatura . ' - ' . $asignaturaData['nombre_asignatura'];
        } else {
            $asignaturaCompleta = $primeraAsignatura . ' - (Asignatura no encontrada)';
        }
    }
    
    if (count($coevaluaciones) > 1) {
        $asignaturaCompleta = 'Múltiples asignaturas (' . count($coevaluaciones) . ')';
    }
    
    // Generar nombre del archivo
    $fechaFormateada = date('Y-m-d', strtotime($fecha_documento));
    $nombreArchivo = "coevaluacion_{$coordinador['carrera']}_{$fechaFormateada}.pdf";
    
    // Guardar en la base de datos
    $stmt = $pdo->prepare("
        INSERT INTO coevaluacion_repository 
        (nombre_archivo, archivo_pdf, tipo_mime, asignatura, periodo_lectivo, usuario_creacion) 
        VALUES (?, ?, 'application/pdf', ?, ?, ?)
    ");
    
    $resultado = $stmt->execute([
        $nombreArchivo,
        $contenidoPDF,
        $asignaturaCompleta,
        $periodoLectivo,
        $_SESSION['usuario']['codigo']
    ]);
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'PDF guardado exitosamente',
            'archivo' => $nombreArchivo,
            'id' => $pdo->lastInsertId()
        ]);
    } else {
        throw new Exception('Error al guardar el PDF en la base de datos');
    }
    
} catch (PDOException $e) {
    error_log("Error de base de datos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    
} catch (Exception $e) {
    error_log("Error general: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>