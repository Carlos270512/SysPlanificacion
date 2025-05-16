<?php
header('Content-Type: application/json');

if (!isset($_GET['id_unidad'])) {
    echo json_encode(['error' => 'Falta el parámetro id_unidad']);
    exit;
}

$id_unidad = $_GET['id_unidad'];

$pdo = require_once __DIR__ . '/../config/conexion.php';

$stmt = $pdo->prepare("SELECT * FROM unidad WHERE id_unidad = ?");
$stmt->execute([$id_unidad]);
$unidad = $stmt->fetch(PDO::FETCH_ASSOC);

if ($unidad) {
    echo json_encode($unidad);
} else {
    echo json_encode(['error' => 'Unidad no encontrada']);
}