<?php
require_once __DIR__ . '/../../config/conexion.php';
header('Content-Type: application/json');

$id_semana = $_POST['id_semana'] ?? null;
if (!$id_semana) {
    echo json_encode(['success' => false, 'message' => 'ID de semana no especificado']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM semana WHERE id_semana = ?");
$ok = $stmt->execute([$id_semana]);

if ($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la semana']);
}