<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/conexion.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Habilitar errores en desarrollo (opcional)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validar archivo recibido
if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
    header("Location: /SysPlanificacion/public/Administrador/subirExcel.php?error_subida=1");
    exit();
}

$archivo = $_FILES['archivo_excel']['tmp_name'];

// Validar que el archivo sea un Excel válido
try {
    $documento = IOFactory::load($archivo);
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    header("Location:/SysPlanificacion/public/Administrador/subirExcel.php?error_formato=1");
    exit();
}

$hoja = $documento->getActiveSheet();
$filas = $hoja->toArray(null, true, true, true);

// Encabezados esperados (agregado PERIODO LECTIVO)
$encabezadosValidos = [
    'CODIGO', 'ASIGNATURA', 'HORARIO', 'JORNADA', 'PERIODO LECTIVO', 'AULA', 'NIVEL',
    'FECHA INICIO', 'FECHA FIN', 'PROFESOR'
];

// Validar encabezados
$encabezadosArchivo = array_map(function ($valor) {
    return strtoupper(trim((string)($valor ?? '')));
}, $filas[1] ?? []);

if (array_diff($encabezadosValidos, $encabezadosArchivo)) {
    header("Location: /SysPlanificacion/public/Administrador/subirExcel.php?error_encabezados=1");
    exit();
}

// Generar un hash único del archivo subido
$hashArchivo = hash_file('sha256', $archivo);

// Verificar si el archivo ya fue subido
session_start();
if (isset($_SESSION['archivos_subidos']) && in_array($hashArchivo, $_SESSION['archivos_subidos'])) {
    header("Location: /SysPlanificacion/public/Administrador/subirExcel.php?archivo_duplicado=1");
    exit();
}

// Guardar el hash del archivo en la sesión
if (!isset($_SESSION['archivos_subidos'])) {
    $_SESSION['archivos_subidos'] = [];
}
$_SESSION['archivos_subidos'][] = $hashArchivo;

// Buscar índices de las columnas
$indices = [];
foreach ($filas[1] as $col => $valor) {
    $valorLimpio = strtoupper(trim((string)($valor ?? '')));
    if (in_array($valorLimpio, $encabezadosValidos)) {
        $indices[$valorLimpio] = $col;
    }
}

$filasConErrores = [];

// Procesar filas
for ($i = 2; $i <= count($filas); $i++) {
    $fila = $filas[$i];
    $erroresFila = [];

    // Extraer valores
    $codigoAsignatura = isset($indices['CODIGO']) ? trim((string)($fila[$indices['CODIGO']] ?? '')) : '';
    $nombreAsignatura = isset($indices['ASIGNATURA']) ? trim((string)($fila[$indices['ASIGNATURA']] ?? '')) : '';
    $profesorRaw = isset($indices['PROFESOR']) ? trim((string)($fila[$indices['PROFESOR']] ?? '')) : '';
    $periodoLectivo = isset($indices['PERIODO LECTIVO']) ? trim((string)($fila[$indices['PERIODO LECTIVO']] ?? '')) : '';
    $aula = isset($indices['AULA']) ? trim((string)($fila[$indices['AULA']] ?? '')) : '';

    // Validar datos obligatorios
    if (!$codigoAsignatura) $erroresFila[] = 'Falta el código de la asignatura';
    if (!$nombreAsignatura) $erroresFila[] = 'Falta el nombre de la asignatura';
    if (!$profesorRaw) $erroresFila[] = 'Falta el profesor';
    if (!$periodoLectivo) $erroresFila[] = 'Falta el periodo lectivo';
    if (!$aula) $erroresFila[] = 'Falta el aula';

    // Validar que el profesor exista
    $codigoProfesor = explode('-', $profesorRaw)[0] ?? '';
    if ($codigoProfesor) {
        $stmtDocente = $pdo->prepare("SELECT COUNT(*) FROM docente WHERE codigo = ?");
        $stmtDocente->execute([$codigoProfesor]);
        if ($stmtDocente->fetchColumn() == 0) {
            $erroresFila[] = "El código de profesor '$codigoProfesor' no existe";
        }
    }

    if (!empty($erroresFila)) {
        $filasConErrores[] = [
            'codigo' => $codigoAsignatura,
            'asignatura' => $nombreAsignatura,
            'horario' => $fila[$indices['HORARIO']] ?? '',
            'jornada' => $fila[$indices['JORNADA']] ?? '',
            'periodo_lectivo' => $periodoLectivo,
            'aula' => $aula,
            'nivel' => $fila[$indices['NIVEL']] ?? '',
            'fecha_inicio' => $fila[$indices['FECHA INICIO']] ?? '',
            'fecha_fin' => $fila[$indices['FECHA FIN']] ?? '',
            'profesor' => $profesorRaw,
            'errores' => implode(', ', $erroresFila)
        ];
        continue;
    }

    try {
        $stmtInsertAsignatura = $pdo->prepare("
            INSERT INTO asignatura (
                codigo, nombre_asignatura, horario, jornada, periodo_academico, aula, nivel, fecha_inicio, fecha_fin, docente_codigo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmtInsertAsignatura->execute([
            $codigoAsignatura,
            $nombreAsignatura,
            $fila[$indices['HORARIO']] ?? null,
            $fila[$indices['JORNADA']] ?? null,
            $periodoLectivo,
            $aula,
            $fila[$indices['NIVEL']] ?? null,
            date('Y-m-d', strtotime($fila[$indices['FECHA INICIO']] ?? '')),
            date('Y-m-d', strtotime($fila[$indices['FECHA FIN']] ?? '')),
            $codigoProfesor
        ]);
    } catch (PDOException $e) {
        $filasConErrores[] = [
            'codigo' => $codigoAsignatura,
            'asignatura' => $nombreAsignatura,
            'horario' => $fila[$indices['HORARIO']] ?? '',
            'jornada' => $fila[$indices['JORNADA']] ?? '',
            'periodo_lectivo' => $periodoLectivo,
            'aula' => $aula,
            'nivel' => $fila[$indices['NIVEL']] ?? '',
            'fecha_inicio' => $fila[$indices['FECHA INICIO']] ?? '',
            'fecha_fin' => $fila[$indices['FECHA FIN']] ?? '',
            'profesor' => $profesorRaw,
            'errores' => 'Error en la base de datos: ' . $e->getMessage()
        ];
    }
}

// Guardar errores en la sesión y redirigir
if (!empty($filasConErrores)) {
    $_SESSION['errores_excel'] = $filasConErrores;
    header("Location: /SysPlanificacion/public/Administrador/subirExcel.php?exito=1&errores=1");
    exit();
}

// Redirigir si todo fue exitoso
header("Location: /SysPlanificacion/public/Administrador/subirExcel.php?exito=1");
exit();