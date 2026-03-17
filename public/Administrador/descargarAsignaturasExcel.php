<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../config/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="plantilla_asignaturas.xlsx"');
header('Cache-Control: max-age=0');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$headers = [
    'Codigo',
    'Asignatura',
    'Horario',
    'Jornada',
    'Periodo Lectivo',
    'Aula',
    'Nivel',
    'Fecha Inicio',
    'Fecha Fin',
    'Profesor'
];
$sheet->fromArray($headers, null, 'A1');

// Obtener datos de la tabla asignatura
$stmt = $pdo->query("SELECT codigo, nombre_asignatura, horario, jornada, periodo_academico, aula, nivel, fecha_inicio, fecha_fin, docente_codigo FROM asignatura");
$rows = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $rows[] = [
        $row['codigo'],
        $row['nombre_asignatura'],
        $row['horario'],
        $row['jornada'],
        $row['periodo_academico'],
        $row['aula'],
        $row['nivel'],
        $row['fecha_inicio'],
        $row['fecha_fin'],
        $row['docente_codigo']
    ];
}
if (!empty($rows)) {
    $sheet->fromArray($rows, null, 'A2');
}

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
