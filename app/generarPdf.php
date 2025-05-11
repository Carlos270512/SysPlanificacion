<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Asegúrate de que la ruta sea correcta

// Verificar si se ha recibido el POST correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos enviados
    $fecha = $_POST['fecha'];
    $docente = $_POST['docente'];
    $asignaturaJson = $_POST['asignatura']; // Recibimos el JSON serializado
    $asignaturaData = json_decode($asignaturaJson, true); // Convertimos el JSON a un array asociativo

    // Extraer los datos de la asignatura
    $asignatura = $asignaturaData['nombre_asignatura'] ?? 'No definida';
    $carrera = $asignaturaData['carrera'] ?? 'No definida';
    $jornada = $asignaturaData['jornada'] ?? 'No definida';
    $horario = $asignaturaData['horario'] ?? 'No definida';
    $nivel = $asignaturaData['nivel'] ?? 'No definida';
    $aula = $asignaturaData['aula'] ?? 'No definida';
    $fechaInicio = $asignaturaData['fecha_inicio'] ?? 'No definida';
    $fechaFin = $asignaturaData['fecha_fin'] ?? 'No definida';

    // Crear instancia de TCPDF
    $pdf = new \TCPDF('L', 'mm', 'A4'); // Orientación horizontal

    // Agregar página
    $pdf->AddPage();

    // Configuración de la fuente para el título
    $pdf->SetFont('helvetica', 'B', 16);

    // Título
    $pdf->Cell(0, 10, 'Formulario - Información General', 0, 1, 'C');

    // Salto de línea
    $pdf->Ln(10);

    // Establecer la fuente para los datos (negrita para etiquetas)
    $pdf->SetFont('helvetica', 'B', 12);

    // Encabezado de la tabla
    $pdf->Cell(50, 10, 'Fecha:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 12); // Cambio de fuente para los valores
    $pdf->Cell(70, 10, $fecha, 1, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 12); // Volver a poner en negrita
    $pdf->Cell(50, 10, 'Docente:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(70, 10, $docente, 1, 1, 'C');

    // Datos
    $pdf->SetFont('helvetica', 'B', 12); // Negrita para las etiquetas
    $pdf->Cell(50, 10, 'Asignatura:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(70, 10, $asignatura, 1, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 10, 'Carrera:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(70, 10, $carrera, 1, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 10, 'Jornada:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(70, 10, $jornada, 1, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 10, 'Horario:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(70, 10, $horario, 1, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 10, 'Nivel:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(70, 10, $nivel, 1, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 10, 'Aula:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(70, 10, $aula, 1, 1, 'C');

    // Fechas
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 10, 'Fecha Inicio:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(70, 10, $fechaInicio, 1, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 10, 'Fecha Fin:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(70, 10, $fechaFin, 1, 1, 'C');

    // Salida del archivo PDF
    $pdf->Output('informacion_general.pdf', 'I');
} else {
    echo "No se enviaron datos POST.";
}
?>