<?php
header('Content-Type: application/json');
$pdo = require_once __DIR__ . '/../config/conexion.php';

// Si viene id_semana, devuelve los datos completos de la semana
if (isset($_GET['id_semana'])) {
    $id_semana = $_GET['id_semana'];
    $stmt = $pdo->prepare("SELECT * FROM semana WHERE id_semana = ?");
    $stmt->execute([$id_semana]);
    $semana = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($semana ?: []);
    exit;
}

// Si viene id_unidad, devuelve las semanas de esa unidad (solo para el carrusel)
if (isset($_GET['id_unidad'])) {
    $id_unidad = $_GET['id_unidad'];
    $stmt = $pdo->prepare("
        SELECT 
            id_semana, 
            fecha_lunes, 
            fecha_viernes
        FROM semana 
        WHERE id_unidad = ?
        ORDER BY fecha_lunes ASC
    ");
    $stmt->execute([$id_unidad]);
    $semanas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($semanas);
    exit;
}

// Si no viene nada válido
echo json_encode([]);
exit;