<?php
require_once __DIR__ . '/../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreDocente = $_POST['nombre_docente'] ?? 'No definido';
    $asignaturaJson = $_POST['asignatura'];
    $asignaturaData = json_decode($asignaturaJson, true);

    $asignatura = $asignaturaData['nombre_asignatura'] ?? 'No definida';
    $codigoAsignatura = $asignaturaData['codigo'] ?? 'No definido';
    $jornada = $asignaturaData['jornada'] ?? 'No definida';
    $nivel = $asignaturaData['nivel'] ?? 'No definida';

    $modalidad = (in_array($jornada, ['PM', 'PN'])) ? 'Presencial' : 'Virtual';

    function nivelALetras($nivel) {
        $niveles = [
            '1' => 'PRIMERO', '2' => 'SEGUNDO', '3' => 'TERCERO', '4' => 'CUARTO',
            '5' => 'Cinco', '6' => 'Seis', '7' => 'Siete', '8' => 'Ocho',
            '9' => 'Nueve', '10' => 'Diez'
        ];
        return $niveles[$nivel] ?? $nivel;
    }
    $nivelLetra = nivelALetras($nivel);

    $pdf = new \TCPDF('L', 'mm', 'A4');
    $pdf->AddPage();

    // Título
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Formulario - Información General', 0, 1, 'C');
    $pdf->Ln(4);

    // Reducir tamaño de letra para la tabla
    $pdf->SetFont('helvetica', '', 9);

    // Definir anchos
    // Total A4 horizontal: 297mm - márgenes
    // Izquierda: Asignatura (etiqueta+valor) = Modalidad+Nivel (etiquetas+valores)
    // Derecha: Código Asignatura (etiqueta+valor) = Docente (etiqueta+valor)
    $w_asig = 110;   // Asignatura (etiqueta+valor)
    $w_cod = 110;    // Código Asignatura (etiqueta+valor)
    $w_mod = 40;     // Modalidad (etiqueta+valor)
    $w_niv = 70;     // Nivel (etiqueta+valor)
    $w_doc = 110;    // Docente (etiqueta+valor)

    // Primera fila: Asignatura | Código Asignatura
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(25, 8, 'Asignatura:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell($w_asig-25, 8, $asignatura, 1, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(35, 8, 'Código Asignatura:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell($w_cod-35, 8, $codigoAsignatura, 1, 1, 'C');

    // Segunda fila: Modalidad + Nivel | Docente
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(20, 8, 'Modalidad:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell($w_mod-20, 8, $modalidad, 1, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(15, 8, 'Nivel:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell($w_niv-15, 8, $nivelLetra, 1, 0, 'C');
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(20, 8, 'Docente:', 1, 0, 'C');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell($w_doc-20, 8, $nombreDocente, 1, 1, 'C');

    $pdf->Output('informacion_general.pdf', 'I');
} else {
    echo "No se enviaron datos POST.";
}
?>