<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    header("Location: ../public/gestionUsuarios.php");
    exit();
}
require __DIR__ . '/../config/conexion.php';

if (isset($_POST['codigo'], $_POST['estado'])) {
    $codigo = $_POST['codigo'];
    $nuevoEstado = ($_POST['estado'] === 'ACTIVO') ? 'ACTIVO' : 'INACTIVO';

    $stmt = $pdo->prepare("UPDATE docente SET estado = ? WHERE codigo = ?");
    $stmt->execute([$nuevoEstado, $codigo]);
}

header("Location: ../public/gestionUsuarios.php");
exit();