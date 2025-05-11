<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/conexion.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_FILES['archivo_excel'])) {
    die('No se recibió ningún archivo.');
}

$archivo = $_FILES['archivo_excel']['tmp_name'];

// Leer el archivo
$documento = IOFactory::load($archivo);
$hoja = $documento->getActiveSheet();
$filas = $hoja->toArray(null, true, true, true);

// Mapeo de columnas válidas
$encabezadosValidos = [
    'CODIGO', 'ASIGNATURA', 'HORARIO', 'JORNADA', 'AULA', 'NIVEL',
    'FECHA INICIO', 'FECHA FIN', 'PROFESOR'
];

// Buscar índices de las columnas
$indices = [];
foreach ($filas[1] as $columna => $valor) {
    $valorLimpio = strtoupper(trim($valor ?? ''));
    if (in_array($valorLimpio, $encabezadosValidos)) {
        $indices[$valorLimpio] = $columna;
    }
}

// Crear un array para almacenar filas con errores
$filasConErrores = [];

// Procesar filas
for ($i = 2; $i <= count($filas); $i++) {
    $fila = $filas[$i];
    $motivoError = ''; // Variable para almacenar el motivo del error

    // Extraer valores
    $codigoAsignatura = isset($indices['CODIGO']) ? trim((string)($fila[$indices['CODIGO']] ?? '')) : null;
    $nombreAsignatura = isset($indices['ASIGNATURA']) ? trim((string)($fila[$indices['ASIGNATURA']] ?? '')) : null;
    $horario = isset($indices['HORARIO']) ? trim((string)($fila[$indices['HORARIO']] ?? '')) : null;
    $jornada = isset($indices['JORNADA']) ? trim((string)($fila[$indices['JORNADA']] ?? '')) : null;
    $aula = isset($indices['AULA']) ? trim((string)($fila[$indices['AULA']] ?? '')) : null;
    $nivel = isset($indices['NIVEL']) ? trim((string)($fila[$indices['NIVEL']] ?? '')) : null;
    $fechaInicio = isset($indices['FECHA INICIO']) ? trim((string)($fila[$indices['FECHA INICIO']] ?? '')) : null;
    $fechaFin = isset($indices['FECHA FIN']) ? trim((string)($fila[$indices['FECHA FIN']] ?? '')) : null;
    $profesorRaw = isset($indices['PROFESOR']) ? trim((string)($fila[$indices['PROFESOR']] ?? '')) : null;

    // Validar datos obligatorios
    if (!$codigoAsignatura) {
        $motivoError = 'Falta el código de la asignatura';
    } elseif (!$nombreAsignatura) {
        $motivoError = 'Falta el nombre de la asignatura';
    } elseif (!$profesorRaw) {
        $motivoError = 'Falta el profesor';
    }

    if ($motivoError) {
        $fila['MOTIVO_ERROR'] = $motivoError; // Agregar el motivo del error a la fila
        $filasConErrores[] = $fila; // Guardar fila con errores
        continue; // Saltar a la siguiente fila
    }

    // Dividir el campo PROFESOR (esperado: CODIGO - NOMBRE)
    $partesProfesor = explode('-', $profesorRaw);
    $codigoDocente = trim($partesProfesor[0] ?? '');
    $nombreDocente = trim($partesProfesor[1] ?? '');

    try {
        // Insertar asignatura
        $stmtInsertAsignatura = $pdo->prepare("
            INSERT INTO asignatura (
                codigo, nombre_asignatura, horario, jornada, aula, nivel, fecha_inicio, fecha_fin, docente_codigo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmtInsertAsignatura->execute([
            $codigoAsignatura,
            $nombreAsignatura,
            $horario,
            $jornada,
            $aula,
            $nivel,
            date('Y-m-d', strtotime($fechaInicio)),
            date('Y-m-d', strtotime($fechaFin)),
            $codigoDocente
        ]);
    } catch (PDOException $e) {
        $fila['MOTIVO_ERROR'] = 'Error en la base de datos: ' . $e->getMessage();
        $filasConErrores[] = $fila; // Guardar fila con errores si ocurre una excepción
    }
}

// Si hay filas con errores, generar un archivo Excel
if (!empty($filasConErrores)) {
    $spreadsheetErrores = new Spreadsheet();
    $hojaErrores = $spreadsheetErrores->getActiveSheet();

    // Copiar encabezados y agregar columna de motivo de error
    $encabezados = $filas[1];
    $encabezados['MOTIVO_ERROR'] = 'Motivo del Error'; // Agregar encabezado para el motivo del error
    $hojaErrores->fromArray(array_values($encabezados), null, 'A1'); // Asegurar que los encabezados sean un array indexado

    // Preparar filas con errores para incluir el motivo
    $filasErroresConMotivo = [];
    foreach ($filasConErrores as $filaConError) {
        $filaError = [];
        foreach ($encabezados as $key => $header) {
            $filaError[] = $filaConError[$key] ?? ''; // Asegurar que las claves coincidan con los encabezados
        }
        $filaError[] = $filaConError['MOTIVO_ERROR'] ?? ''; // Agregar el motivo del error al final
        $filasErroresConMotivo[] = $filaError;
    }

    // Copiar filas con errores al archivo Excel
    $hojaErrores->fromArray($filasErroresConMotivo, null, 'A2');

    // Guardar archivo
    $writer = new Xlsx($spreadsheetErrores);
    $rutaErrores = __DIR__ . '/../public/errores.xlsx';
    $writer->save($rutaErrores);

    // Redirigir con indicador de errores
    header("Location: ../public/subirExcel.php?exito=1&errores=1");
    exit();
}

header("Location: ../public/subirExcel.php?exito=1");
exit();