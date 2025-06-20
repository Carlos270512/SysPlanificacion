<?php
require_once __DIR__ . '/../../config/conexion.php';

$id_semana_linea = intval($_POST['id_semana_linea'] ?? 0);

try {
    $stmt = $pdo->prepare("DELETE FROM semana_linea WHERE id_semana_linea = ?");
    $stmt->execute([$id_semana_linea]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}