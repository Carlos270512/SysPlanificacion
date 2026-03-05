<?php
require_once __DIR__ . '/../../config/conexion.php';

$id_unidad = intval($_GET['id_unidad'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM semana_linea WHERE id_unidad = ? ORDER BY id_semana_linea ASC");
    $stmt->execute([$id_unidad]);
    $semanas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'semanas' => $semanas]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}