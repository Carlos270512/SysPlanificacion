<?php
header('Content-Type: application/json');
$pdo = require_once __DIR__ . '/../config/conexion.php';

// Si viene id_unidad, devuelve los datos de la unidad
if (isset($_GET['id_unidad'])) {
    $id_unidad = $_GET['id_unidad'];
    $stmt = $pdo->prepare("SELECT * FROM unidad WHERE id_unidad = ?");
    $stmt->execute([$id_unidad]);
    $unidad = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($unidad ?: []);
    exit;
}

// Si viene asignatura_codigo, devuelve las unidades de esa asignatura
if (isset($_GET['asignatura_codigo'])) {
    $codigo = $_GET['asignatura_codigo'];
    $stmt = $pdo->prepare("SELECT id_unidad, nombre FROM unidad WHERE asignatura_codigo = ?");
    $stmt->execute([$codigo]);
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($unidades);
    exit;
}

// Si no viene nada válido
echo json_encode([]);
exit;