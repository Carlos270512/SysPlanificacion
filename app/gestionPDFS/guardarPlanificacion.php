<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/PlanificacionRepository.php';

header('Content-Type: application/json');

try {
    if (
        !isset($_POST['unidad_id']) ||
        !isset($_POST['nombre_archivo']) ||
        !isset($_FILES['archivo_pdf'])
    ) {
        throw new Exception('Faltan datos requeridos.');
    }

    $unidad_id = intval($_POST['unidad_id']);
    $nombre_archivo = trim($_POST['nombre_archivo']);
    $tipo_mime = $_FILES['archivo_pdf']['type'] ?? 'application/pdf';
    $archivo_pdf = file_get_contents($_FILES['archivo_pdf']['tmp_name']);

    // Obtener el usuario (docente) asociado a la unidad usando el repositorio
    $repo = new PlanificacionRepository($pdo);
    $docente = $repo->getDocentePorUnidad($unidad_id);
    $usuario = $docente ? $docente['codigo'] : null;

    // Obtener datos adicionales para el repositorio
    $datosUnidad = $repo->getDatosUnidadCompletos($unidad_id);
    $asignatura = $datosUnidad ? $datosUnidad['asignatura'] : null;
    $periodo_lectivo = $datosUnidad ? $datosUnidad['periodo_lectivo'] : date('Y');

    // Iniciar transacción para mantener consistencia
    $pdo->beginTransaction();

    // Verificar si ya existe una planificación para esa unidad
    $stmt = $pdo->prepare("SELECT id_planificacion FROM planificaciones WHERE unidad_id = ?");
    $stmt->execute([$unidad_id]);
    $existe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        // Actualizar PDF existente en planificaciones
        $stmt = $pdo->prepare("UPDATE planificaciones 
            SET nombre_archivo = ?, archivo_pdf = ?, tipo_mime = ?, fecha_actualizacion = NOW(), usuario_actualizacion = ?
            WHERE unidad_id = ?");
        $stmt->execute([$nombre_archivo, $archivo_pdf, $tipo_mime, $usuario, $unidad_id]);
        
        $mensaje = 'PDF actualizado correctamente.';
    } else {
        // Insertar nuevo PDF en planificaciones
        $stmt = $pdo->prepare("INSERT INTO planificaciones (unidad_id, nombre_archivo, archivo_pdf, tipo_mime, usuario_creacion) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$unidad_id, $nombre_archivo, $archivo_pdf, $tipo_mime, $usuario]);
        
        $mensaje = 'PDF guardado correctamente.';
    }

    // Verificar si ya existe una planificación en el repositorio para esa unidad
    $stmt = $pdo->prepare("SELECT id_repository FROM planificaciones_repository WHERE unidad_id = ?");
    $stmt->execute([$unidad_id]);
    $existeRepository = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existeRepository) {
        // Actualizar PDF existente en repositorio
        $stmt = $pdo->prepare("UPDATE planificaciones_repository 
            SET nombre_archivo = ?, archivo_pdf = ?, tipo_mime = ?, asignatura = ?, periodo_lectivo = ?, fecha_actualizacion = NOW(), usuario_actualizacion = ?
            WHERE unidad_id = ?");
        $stmt->execute([$nombre_archivo, $archivo_pdf, $tipo_mime, $asignatura, $periodo_lectivo, $usuario, $unidad_id]);
        
        $mensajeRepository = 'actualizado en repositorio';
    } else {
        // Insertar nuevo PDF en repositorio
        $stmt = $pdo->prepare("INSERT INTO planificaciones_repository 
            (unidad_id, nombre_archivo, archivo_pdf, tipo_mime, asignatura, periodo_lectivo, usuario_creacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$unidad_id, $nombre_archivo, $archivo_pdf, $tipo_mime, $asignatura, $periodo_lectivo, $usuario]);
        
        $mensajeRepository = 'guardado en repositorio';
    }

    // Confirmar transacción
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => $mensaje . ' También ' . $mensajeRepository . '.']);

} catch (Exception $e) {
    // Revertir cambios en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}