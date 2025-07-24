<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $codigo_original = trim($_POST['codigo_original'] ?? $codigo);
    $carrera = trim($_POST['carrera'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
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

    // Verificar duplicados de correo (excepto el propio)
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM docente WHERE correo = ? AND codigo != ?");
    $stmtCheck->execute([$correo, $codigo_original]);
    if ($stmtCheck->fetchColumn() > 0) {
        $_SESSION['errores_excel'][] = [
            'codigo' => $codigo,
            'carrera' => $carrera,
            'titulo' => $titulo,
            'nombre' => $nombre,
            'correo' => $correo,
            'rol' => $rol,
            'errores' => 'Correo duplicado'
        ];
        header("Location: /SysPlanificacion/public/Administrador/gestionUsuarios.php?errores=1");
        exit();
    }

    // Construir consulta de actualización
    if ($password !== '') {
        $stmt = $pdo->prepare("UPDATE docente SET codigo = ?, carrera = ?, nombre = ?, titulo = ?, rol = ?, correo = ?, password = ? WHERE codigo = ?");
        $result = $stmt->execute([$codigo, $carrera, $nombre, $titulo, $rol, $correo, $password, $codigo_original]);
    } else {
        $stmt = $pdo->prepare("UPDATE docente SET codigo = ?, carrera = ?, nombre = ?, titulo = ?, rol = ?, correo = ? WHERE codigo = ?");
        $result = $stmt->execute([$codigo, $carrera, $nombre, $titulo, $rol, $correo, $codigo_original]);
    }

    header("Location: /SysPlanificacion/public/Administrador/gestionUsuarios.php?exito=1");
    exit();
}
header("Location: /SysPlanificacion/public/Administrador/gestionUsuarios.php");
exit();
