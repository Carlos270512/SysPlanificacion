<?php
require_once __DIR__ . '/../../config/conexion.php';
header('Content-Type: application/json');

$id_unidad = $_GET['id_unidad'] ?? null;
if (!$id_unidad) {
    echo json_encode(['success' => false, 'message' => 'Unidad no especificada']);
    exit;
}

// Trae la última semana creada para esa unidad (puedes cambiar el criterio)
$stmt = $pdo->prepare("SELECT * FROM semana WHERE id_unidad = ? ORDER BY id_semana DESC LIMIT 1");
$stmt->execute([$id_unidad]);
$semana = $stmt->fetch(PDO::FETCH_ASSOC);

if ($semana) {
    echo json_encode(['success' => true, 'semana' => $semana]);
} else {
    echo json_encode(['success' => false, 'message' => 'No hay semana registrada']);
}