<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'COORDINADOR') {
    header("Location: ../../public/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../public/Coordinador/dashboard.php");
    exit();
}

$pdo = null;
$transactionStarted = false;

try {
    $pdo = require_once __DIR__ . '/../../config/conexion.php';
    
    // Validar datos requeridos
    if (empty($_POST['docente_codigo']) || empty($_POST['asignatura_codigo']) || 
        empty($_POST['campo_corregir']) || empty($_POST['descripcion_observacion'])) {
        throw new Exception("Faltan datos obligatorios para guardar la observación.");
    }
    
    $docente_codigo = $_POST['docente_codigo'];
    $asignatura_codigo = $_POST['asignatura_codigo'];
    $campo_corregir = $_POST['campo_corregir'];
    $descripcion_observacion = $_POST['descripcion_observacion'];
    $usuario_revisa = $_SESSION['usuario']['nombre'] ?? $_SESSION['usuario']['codigo'];
    
    // Construir la observación completa
    $observacion_completa = "Campo a corregir: " . ucfirst($campo_corregir) . "\n\n" . 
                           "Descripción: " . $descripcion_observacion;
    
    // Obtener todas las planificaciones de la asignatura y docente
    $sql_planificaciones = "
        SELECT DISTINCT 
            p.id_planificacion,
            p.unidad_id,
            p.nombre_archivo,
            u.asignatura_codigo
        FROM planificaciones p
        INNER JOIN unidad u ON p.unidad_id = u.id_unidad
        INNER JOIN asignatura a ON u.asignatura_codigo = a.codigo
        WHERE a.codigo = :asignatura_codigo 
        AND a.docente_codigo = :docente_codigo
        AND p.estado = 'A'
    ";
    
    $stmt_planificaciones = $pdo->prepare($sql_planificaciones);
    $stmt_planificaciones->execute([
        'asignatura_codigo' => $asignatura_codigo,
        'docente_codigo' => $docente_codigo
    ]);
    
    $planificaciones = $stmt_planificaciones->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($planificaciones)) {
        throw new Exception("No se encontraron planificaciones para esta asignatura y docente.");
    }
    
    // Iniciar transacción DESPUÉS de verificar que hay planificaciones
    $pdo->beginTransaction();
    $transactionStarted = true;
    
    // Preparar la consulta de inserción
    $sql_insert = "
        INSERT INTO observaciones_planificacion (
            planificacion_id,
            unidad_id,
            asignatura_codigo,
            docente_codigo,
            nombre_archivo,
            observacion,
            usuario_revisa,
            estado
        ) VALUES (
            :planificacion_id,
            :unidad_id,
            :asignatura_codigo,
            :docente_codigo,
            :nombre_archivo,
            :observacion,
            :usuario_revisa,
            'corregir'
        )
    ";
    
    $stmt_insert = $pdo->prepare($sql_insert);
    
    $observaciones_guardadas = 0;
    
    // Insertar observación para cada planificación encontrada
    foreach ($planificaciones as $planificacion) {
        // Verificar si ya existe una observación pendiente para esta planificación
        $sql_check = "
            SELECT COUNT(*) as total 
            FROM observaciones_planificacion 
            WHERE planificacion_id = :planificacion_id 
            AND estado = 'corregir'
        ";
        
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute(['planificacion_id' => $planificacion['id_planificacion']]);
        $existe = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Solo insertar si no existe una observación pendiente
        if ($existe['total'] == 0) {
            $result = $stmt_insert->execute([
                'planificacion_id' => $planificacion['id_planificacion'],
                'unidad_id' => $planificacion['unidad_id'],
                'asignatura_codigo' => $planificacion['asignatura_codigo'],
                'docente_codigo' => $docente_codigo,
                'nombre_archivo' => $planificacion['nombre_archivo'],
                'observacion' => $observacion_completa,
                'usuario_revisa' => $usuario_revisa
            ]);
            
            if ($result) {
                $observaciones_guardadas++;
            }
        }
    }
    
    // Verificar si se guardó al menos una observación
    if ($observaciones_guardadas === 0) {
        throw new Exception("No se pudo guardar ninguna observación. Es posible que ya existan observaciones pendientes para todas las planificaciones.");
    }
    
    $pdo->commit();
    $transactionStarted = false;
    
    // En lugar de redireccionar, enviar respuesta JSON para el iframe
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => "Observación enviada exitosamente. Se crearon {$observaciones_guardadas} observaciones para las planificaciones de esta asignatura."
    ]);
    exit();
    
} catch (Exception $e) {
    // Solo hacer rollback si la transacción fue iniciada
    if ($pdo && $transactionStarted) {
        try {
            $pdo->rollBack();
        } catch (PDOException $rollbackError) {
            // Log del error de rollback si es necesario
            error_log("Error en rollback: " . $rollbackError->getMessage());
        }
    }
    
    // Enviar respuesta JSON de error
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => "Error al guardar la observación: " . $e->getMessage()
    ]);
    exit();
}
?>