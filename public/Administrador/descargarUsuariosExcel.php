<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../config/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="plantilla_usuarios.xlsx"');
header('Cache-Control: max-age=0');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$headers = [
    'Código',
    'Carrera',
    'Título',
    'Nombre',
    'Correo',
    'Rol',
    'Estado'
];
$sheet->fromArray($headers, null, 'A1');

// Obtener datos de la tabla docente
$stmt = $pdo->query("SELECT codigo, carrera, titulo, nombre, correo, rol, estado FROM docente");
$rows = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $rows[] = [
        $row['codigo'],
        $row['carrera'],
        $row['titulo'],
        $row['nombre'],
        $row['correo'],
        $row['rol'],
        $row['estado']
    ];
}
if (!empty($rows)) {
    $sheet->fromArray($rows, null, 'A2');
}

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
