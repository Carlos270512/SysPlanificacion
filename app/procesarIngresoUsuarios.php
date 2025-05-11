<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/conexion.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Habilitar errores en desarrollo (opcional)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validar archivo recibido
if (!isset($_FILES['archivo_excel'])) {
    die('No se recibió ningún archivo.');
}

$archivo = $_FILES['archivo_excel']['tmp_name'];

// Cargar el archivo
$documento = IOFactory::load($archivo);
$hoja = $documento->getActiveSheet();
$filas = $hoja->toArray(null, true, true, true);

// Encabezados esperados
$encabezadosValidos = [
    'CODIGO',
    'CARRERA',
    'TITULO',
    'NOMBRES Y APELLIDOS',
    'DIRECCION ELECTRONICA INSTITUCIONAL',
    'ROL',
    'PASSWORD'
];

// Buscar índices de las columnas
$indices = [];
foreach ($filas[1] as $col => $valor) {
    $valorLimpio = strtoupper(trim($valor ?? ''));
    if (in_array($valorLimpio, $encabezadosValidos)) {
        $indices[$valorLimpio] = $col;
    }
}

$filasConErrores = [];

for ($i = 2; $i <= count($filas); $i++) {
    $fila = $filas[$i];

    $codigo = trim((string)($fila[$indices['CODIGO']] ?? ''));
    $carrera = trim((string)($fila[$indices['CARRERA']] ?? ''));
    $titulo = trim((string)($fila[$indices['TITULO']] ?? ''));
    $nombre = trim((string)($fila[$indices['NOMBRES Y APELLIDOS']] ?? ''));
    $correo = trim((string)($fila[$indices['DIRECCION ELECTRONICA INSTITUCIONAL']] ?? ''));
    $rol = trim((string)($fila[$indices['ROL']] ?? ''));
    $password = trim((string)($fila[$indices['PASSWORD']] ?? ''));

    // Validación básica
    if (!$codigo || !$carrera || !$titulo || !$nombre || !$correo || !$rol || !$password) {
        $filasConErrores[] = $fila;
        continue;
    }

    try {
        // Validar si ya existe el código o correo
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM docente WHERE codigo = ? OR correo = ?");
        $stmtCheck->execute([$codigo, $correo]);

        if ($stmtCheck->fetchColumn() > 0) {
            $filasConErrores[] = $fila;
            continue;
        }

        // Insertar sin fecha_ingreso
        $stmtInsert = $pdo->prepare("
            INSERT INTO docente (codigo, carrera, nombre, titulo, rol, correo, password)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmtInsert->execute([
            $codigo,
            $carrera,
            $nombre,
            $titulo,
            $rol,
            $correo,
            $password
        ]);

    } catch (PDOException $e) {
        $filasConErrores[] = $fila;
    }
}

// Generar archivo de errores si los hay
if (!empty($filasConErrores)) {
    $spreadsheetErrores = new Spreadsheet();
    $hojaErrores = $spreadsheetErrores->getActiveSheet();

    $hojaErrores->fromArray($filas[1], null, 'A1');
    $hojaErrores->fromArray($filasConErrores, null, 'A2');

    $writer = new Xlsx($spreadsheetErrores);
    $rutaErrores = __DIR__ . '/../public/errores.xlsx';
    $writer->save($rutaErrores);

    header("Location: ../public/subirExcel.php?exito=1&errores=1");
    exit();
}

// Redirigir si todo fue exitoso
header("Location: ../public/subirExcel.php?exito=1");
exit();
