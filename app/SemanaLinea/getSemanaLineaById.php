<?php
require_once __DIR__ . '/../../config/conexion.php';

$id_semana_linea = intval($_GET['id_semana_linea'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM semana_linea WHERE id_semana_linea = ?");
    $stmt->execute([$id_semana_linea]);
    $semana = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'semana' => $semana]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}