<?php
session_start();

if (!isset($_SESSION['errores_excel']) || empty($_SESSION['errores_excel'])) {
    die('No hay errores para descargar.');
}

require_once __DIR__ . '/../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$encabezados = [
    'Código', 'Asignatura', 'Horario', 'Jornada', 'Periodo Lectivo', 'Aula',
    'Nivel', 'Fecha Inicio', 'Fecha Fin', 'Profesor', 'Errores'
];
$sheet->fromArray($encabezados, null, 'A1');

// Formato de negrita y fondo para encabezados
$headerStyle = [
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D9D9D9']
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
];
$sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

// Datos
$fila = 2;
foreach ($_SESSION['errores_excel'] as $err) {
    $sheet->setCellValue("A{$fila}", $err['codigo']);
    $sheet->setCellValue("B{$fila}", $err['asignatura']);
    $sheet->setCellValue("C{$fila}", $err['horario']);
    $sheet->setCellValue("D{$fila}", $err['jornada']);
    $sheet->setCellValue("E{$fila}", $err['periodo_lectivo']);
    $sheet->setCellValue("F{$fila}", $err['aula']);
    $sheet->setCellValue("G{$fila}", $err['nivel']);
    $sheet->setCellValue("H{$fila}", $err['fecha_inicio']);
    $sheet->setCellValue("I{$fila}", $err['fecha_fin']);
    $sheet->setCellValue("J{$fila}", $err['profesor']);
    $sheet->setCellValue("K{$fila}", $err['errores']);
    $fila++;
}

// Bordes para todas las celdas con datos
$ultimaFila = $fila - 1;
$sheet->getStyle("A1:K{$ultimaFila}")->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
]);

// Descargar
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="errores_excel.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;