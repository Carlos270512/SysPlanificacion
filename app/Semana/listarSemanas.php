<?php
require_once __DIR__ . '/../../config/conexion.php';
header('Content-Type: application/json');

$id_unidad = $_GET['id_unidad'] ?? null;
if (!$id_unidad) {
    echo json_encode(['success' => false, 'message' => 'Unidad no especificada', 'semanas' => []]);
    exit;
}

$stmt = $pdo->prepare("SELECT id_semana, fecha_semana, semana_fin FROM semana WHERE id_unidad = ? ORDER BY id_semana ASC");
$stmt->execute([$id_unidad]);
$semanas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'semanas' => $semanas]);