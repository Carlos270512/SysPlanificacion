<?php
header('Content-Type: application/json');

try {
    $pdo = require_once __DIR__ . '/../../config/conexion.php';
    
    if (!isset($_GET['codigo_docente']) || empty($_GET['codigo_docente'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Código de docente no proporcionado'
        ]);
        exit;
    }
    
    $codigoDocente = $_GET['codigo_docente'];
    
    // Obtener asignaturas del docente
    $stmt = $pdo->prepare("
        SELECT 
            codigo,
            nombre_asignatura,
            horario,
            jornada,
            periodo_academico,
            aula,
            nivel,
            fecha_inicio,
            fecha_fin
        FROM asignatura 
        WHERE docente_codigo = ? 
        ORDER BY nombre_asignatura ASC
    ");
    
    $stmt->execute([$codigoDocente]);
    $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'asignaturas' => $asignaturas,
        'total' => count($asignaturas)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener asignaturas: ' . $e->getMessage()
    ]);
}
