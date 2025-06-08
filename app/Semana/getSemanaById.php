<?php
require_once __DIR__ . '/../../config/conexion.php';
header('Content-Type: application/json');

$id_semana = $_GET['id_semana'] ?? null;
if (!$id_semana) {
    echo json_encode(['success' => false, 'message' => 'ID de semana no especificado']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM semana WHERE id_semana = ?");
$stmt->execute([$id_semana]);
$semana = $stmt->fetch(PDO::FETCH_ASSOC);

if ($semana) {
    echo json_encode(['success' => true, 'semana' => $semana]);
} else {
    echo json_encode(['success' => false, 'message' => 'Semana no encontrada']);
}