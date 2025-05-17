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
if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
    header("Location: ../public/gestionUsuarios.php?error_subida=1");
    exit();
}

$archivo = $_FILES['archivo_excel']['tmp_name'];

// Validar que el archivo sea un Excel válido
try {
    $documento = IOFactory::load($archivo);
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
    header("Location: ../public/gestionUsuarios.php?error_formato=1");
    exit();
}

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

// Validar encabezados
$encabezadosArchivo = array_map(function ($valor) {
    return strtoupper(trim((string)($valor ?? '')));
}, $filas[1] ?? []);

if (array_diff($encabezadosValidos, $encabezadosArchivo)) {
    header("Location: ../public/gestionUsuarios.php?error_encabezados=1");
    exit();
}

// Generar un hash único del archivo subido
$hashArchivo = hash_file('sha256', $archivo);

// Verificar si el archivo ya fue subido
session_start();
if (isset($_SESSION['ultimo_hash']) && $_SESSION['ultimo_hash'] === $hashArchivo) {
    header("Location: ../public/gestionUsuarios.php?archivo_subido=1");
    exit();
}

// Actualizar el hash en la sesión
$_SESSION['ultimo_hash'] = $hashArchivo;

// Buscar índices de las columnas
$indices = [];
foreach ($filas[1] as $col => $valor) {
    $valorLimpio = strtoupper(trim((string)($valor ?? '')));
    if (in_array($valorLimpio, $encabezadosValidos)) {
        $indices[$valorLimpio] = $col;
    }
}
$filasConErrores = [];

for ($i = 2; $i <= count($filas); $i++) {
    $fila = $filas[$i];

    $codigo = isset($fila[$indices['CODIGO']]) ? trim((string)$fila[$indices['CODIGO']]) : '';
    $carrera = isset($fila[$indices['CARRERA']]) ? trim((string)$fila[$indices['CARRERA']]) : '';
    $titulo = isset($fila[$indices['TITULO']]) ? trim((string)$fila[$indices['TITULO']]) : '';
    $nombre = isset($fila[$indices['NOMBRES Y APELLIDOS']]) ? trim((string)$fila[$indices['NOMBRES Y APELLIDOS']]) : '';
    $correo = isset($fila[$indices['DIRECCION ELECTRONICA INSTITUCIONAL']]) ? trim((string)$fila[$indices['DIRECCION ELECTRONICA INSTITUCIONAL']]) : '';
    $rol = isset($fila[$indices['ROL']]) ? trim((string)$fila[$indices['ROL']]) : '';
    $password = isset($fila[$indices['PASSWORD']]) ? trim((string)$fila[$indices['PASSWORD']]) : '';

    $erroresFila = [];
    if (!$codigo) $erroresFila[] = "Código vacío";
    if (!$carrera) $erroresFila[] = "Carrera vacía";
    if (!$titulo) $erroresFila[] = "Título vacío";
    if (!$nombre) $erroresFila[] = "Nombre vacío";
    if (!$correo) $erroresFila[] = "Correo vacío";
    if (!$rol) $erroresFila[] = "Rol vacío";
    if (!$password) $erroresFila[] = "Password vacío";

    // Validación básica
    if (count($erroresFila) > 0) {
        $filasConErrores[] = [
            'codigo' => $codigo,
            'carrera' => $carrera,
            'titulo' => $titulo,
            'nombre' => $nombre,
            'correo' => $correo,
            'rol' => $rol,
            'errores' => implode(', ', $erroresFila)
        ];
        continue;
    }

    try {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM docente WHERE codigo = ? OR correo = ?");
        $stmtCheck->execute([$codigo, $correo]);
        if ($stmtCheck->fetchColumn() > 0) {
            $filasConErrores[] = [
                'codigo' => $codigo,
                'carrera' => $carrera,
                'titulo' => $titulo,
                'nombre' => $nombre,
                'correo' => $correo,
                'rol' => $rol,
                'errores' => 'Código o correo duplicado'
            ];
            continue;
        }

        $stmtInsert = $pdo->prepare("
            INSERT INTO docente (codigo, carrera, nombre, titulo, rol, correo, password)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtInsert->execute([
            $codigo, $carrera, $nombre, $titulo, $rol, $correo, $password
        ]);
    } catch (PDOException $e) {
        $filasConErrores[] = [
            'codigo' => $codigo,
            'carrera' => $carrera,
            'titulo' => $titulo,
            'nombre' => $nombre,
            'correo' => $correo,
            'rol' => $rol,
            'errores' => 'Error en base de datos'
        ];
    }
}

// Guardar errores en la sesión y redirigir
if (!empty($filasConErrores)) {
    $_SESSION['errores_excel'] = $filasConErrores;
    header("Location: ../public/gestionUsuarios.php?exito=1&errores=1");
    exit();
}

header("Location: ../public/gestionUsuarios.php?exito=1");
exit();