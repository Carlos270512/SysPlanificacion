<?php
session_start();
require_once 'NotificacionesModel.php';

// Configurar headers para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Obtener el código del docente de la sesión
$codigoDocente = $_SESSION['usuario']['codigo'] ?? null;

if (!$codigoDocente) {
    echo json_encode(['success' => false, 'message' => 'Código de docente no encontrado']);
    exit;
}

// Obtener la acción solicitada
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $notificacionesModel = new NotificacionesModel();

    switch ($action) {
        case 'obtener':
            obtenerNotificaciones($notificacionesModel, $codigoDocente);
            break;

        case 'marcarLeidas':
            marcarTodasComoLeidas($notificacionesModel, $codigoDocente);
            break;

        case 'marcarUna':
            marcarUnaComoLeida($notificacionesModel, $codigoDocente);
            break;

        case 'detalle':
            obtenerDetalleObservacion($notificacionesModel, $codigoDocente);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
}

/**
 * Obtener todas las notificaciones del docente
 */
function obtenerNotificaciones($model, $codigoDocente) {
    try {
        $notificaciones = $model->obtenerNotificacionesPorDocente($codigoDocente);
        $total = $model->contarNotificacionesPorDocente($codigoDocente);

        // Convertir objetos Notificacion a arrays
        $notificacionesArray = [];
        foreach ($notificaciones as $notificacion) {
            $notificacionesArray[] = $notificacion->toArray();
        }

        echo json_encode([
            'success' => true,
            'total' => $total,
            'notificaciones' => $notificacionesArray,
            'message' => 'Notificaciones obtenidas correctamente'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al obtener notificaciones: ' . $e->getMessage()
        ]);
    }
}

/**
 * Marcar todas las observaciones como corregidas
 */
function marcarTodasComoLeidas($model, $codigoDocente) {
    try {
        $resultado = $model->marcarTodasComoCorregidas($codigoDocente);

        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Todas las observaciones han sido marcadas como corregidas'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudieron marcar las observaciones como corregidas'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al marcar como corregidas: ' . $e->getMessage()
        ]);
    }
}

/**
 * Marcar una observación específica como corregida
 */
function marcarUnaComoLeida($model, $codigoDocente) {
    $idObservacion = $_POST['id_observacion'] ?? null;

    if (!$idObservacion) {
        echo json_encode(['success' => false, 'message' => 'ID de observación requerido']);
        return;
    }

    try {
        $resultado = $model->marcarObservacionCorregida($idObservacion, $codigoDocente);

        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Observación marcada como corregida'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo marcar la observación como corregida'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al marcar observación: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtener detalle de una observación específica
 */
function obtenerDetalleObservacion($model, $codigoDocente) {
    $idObservacion = $_GET['id_observacion'] ?? null;

    if (!$idObservacion) {
        echo json_encode(['success' => false, 'message' => 'ID de observación requerido']);
        return;
    }

    try {
        $observacion = $model->obtenerObservacion($idObservacion, $codigoDocente);

        if ($observacion) {
            echo json_encode([
                'success' => true,
                'observacion' => $observacion->toArray(),
                'message' => 'Observación obtenida correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Observación no encontrada'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener observación: ' . $e->getMessage()
        ]);
    }
}
?>