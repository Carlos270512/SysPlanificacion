<?php
require_once __DIR__ . '/../../config/conexion.php';
header('Content-Type: application/json');

$id_unidad = isset($_GET['id_unidad']) ? intval($_GET['id_unidad']) : 0;
if ($id_unidad) {
    $stmt = $pdo->prepare("SELECT id_semana, fecha_semana, actividades_previas FROM semana WHERE id_unidad = ? ORDER BY fecha_semana ASC");
    $stmt->execute([$id_unidad]);
    $semanas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($semanas);
    exit;
}
echo json_encode([]);