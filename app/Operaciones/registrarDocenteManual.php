<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $carrera = trim($_POST['carrera'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
    // Si el campo está vacío, se asigna null
    $fecha_ingreso = (isset($_POST['fecha_ingreso']) && $_POST['fecha_ingreso'] !== '') ? $_POST['fecha_ingreso'] : null;
    $rol = trim($_POST['rol'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $errores = [];
    if (!$codigo) $errores[] = "Código vacío";
    if (!$carrera) $errores[] = "Carrera vacía";
    if (!$nombre) $errores[] = "Nombre vacío";
    if (!$titulo) $errores[] = "Título vacío";
    if (!$rol) $errores[] = "Rol vacío";
    if (!$correo) $errores[] = "Correo vacío";
    if (!$password) $errores[] = "Contraseña vacía";

    if (!empty($errores)) {
        $_SESSION['errores_excel'][] = [
            'codigo' => $codigo,
            'carrera' => $carrera,
            'titulo' => $titulo,
            'nombre' => $nombre,
            'correo' => $correo,
            'rol' => $rol,
            'errores' => implode(', ', $errores)
        ];
        header("Location: /SysPlanificacion/public/Administrador/gestionUsuarios.php?errores=1");
        exit();
    }

    // Verificar duplicados
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM docente WHERE codigo = ? OR correo = ?");
    $stmtCheck->execute([$codigo, $correo]);
    if ($stmtCheck->fetchColumn() > 0) {
        $_SESSION['errores_excel'][] = [
            'codigo' => $codigo,
            'carrera' => $carrera,
            'titulo' => $titulo,
            'nombre' => $nombre,
            'correo' => $correo,
            'rol' => $rol,
            'errores' => 'Código o correo duplicado'
        ];
        header("Location: /SysPlanificacion/public/Administrador/gestionUsuarios.php?errores=1");
        exit();
    }

    // Guardar con hash seguro
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO docente (codigo, carrera, nombre, titulo, fecha_ingreso, rol, correo, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$codigo, $carrera, $nombre, $titulo, $fecha_ingreso, $rol, $correo, $passwordHash]);

    header("Location: /SysPlanificacion/public/Administrador/gestionUsuarios.php?exito=1");
    exit();
}
header("Location: /SysPlanificacion/public/Administrador/gestionUsuarios.php");
exit();