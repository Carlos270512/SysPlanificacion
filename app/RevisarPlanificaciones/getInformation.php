<?php
function getDocenteYAsignatura($pdo, $docente_codigo, $asignatura_codigo) {
    // Obtener datos del docente - CORREGIDO: incluir codigo
    $stmtDoc = $pdo->prepare("SELECT codigo, nombre FROM docente WHERE codigo = ?");
    $stmtDoc->execute([$docente_codigo]);
    $docente = $stmtDoc->fetch(PDO::FETCH_ASSOC);

    // Obtener datos de la asignatura
    $stmtAsig = $pdo->prepare("SELECT * FROM asignatura WHERE codigo = ?");
    $stmtAsig->execute([$asignatura_codigo]);
    $asignatura = $stmtAsig->fetch(PDO::FETCH_ASSOC);

    return [
        'docente' => $docente,
        'asignatura' => $asignatura
    ];
}

function getUnidadesPorAsignatura($pdo, $asignatura_codigo) {
    $stmt = $pdo->prepare("SELECT id_unidad, numero_unidad, nombre FROM unidad WHERE asignatura_codigo = ? ORDER BY numero_unidad ASC");
    $stmt->execute([$asignatura_codigo]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSemanasPorUnidad($pdo, $id_unidad) {
    $stmt = $pdo->prepare("SELECT id_semana, fecha_semana, semana_fin FROM semana WHERE id_unidad = ? ORDER BY fecha_semana ASC");
    $stmt->execute([$id_unidad]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}