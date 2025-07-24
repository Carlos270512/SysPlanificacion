<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once 'Notificacion.php';

class NotificacionesModel {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Obtener observaciones pendientes para un docente específico
     */
    public function obtenerNotificacionesPorDocente($codigoDocente) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    op.id_observacion,
                    op.planificacion_id,
                    op.unidad_id,
                    op.asignatura_codigo,
                    op.docente_codigo,
                    op.nombre_archivo,
                    op.observacion,
                    op.usuario_revisa,
                    op.fecha_observacion,
                    op.fecha_correccion,
                    op.estado,
                    a.nombre_asignatura as asignatura_nombre,
                    u.nombre as unidad_nombre
                FROM observaciones_planificacion op
                INNER JOIN planificaciones p ON op.planificacion_id = p.id_planificacion
                INNER JOIN unidad u ON p.unidad_id = u.id_unidad
                INNER JOIN asignatura a ON u.asignatura_codigo = a.codigo
                WHERE a.docente_codigo = ? 
                AND op.estado = 'corregir'
                AND p.estado = 'A'
                ORDER BY op.fecha_observacion DESC
            ");

            $stmt->execute([$codigoDocente]);

            $notificaciones = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $notificaciones[] = new Notificacion($row);
            }

            return $notificaciones;
        } catch (Exception $e) {
            error_log("Error al obtener notificaciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar observaciones pendientes para un docente
     */
    public function contarNotificacionesPorDocente($codigoDocente) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM observaciones_planificacion op
                INNER JOIN planificaciones p ON op.planificacion_id = p.id_planificacion
                INNER JOIN unidad u ON p.unidad_id = u.id_unidad
                INNER JOIN asignatura a ON u.asignatura_codigo = a.codigo
                WHERE a.docente_codigo = ? 
                AND op.estado = 'corregir'
                AND p.estado = 'A'
            ");

            $stmt->execute([$codigoDocente]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (Exception $e) {
            error_log("Error al contar notificaciones: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Marcar todas las observaciones de un docente como corregidas
     */
    public function marcarTodasComoCorregidas($codigoDocente) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                UPDATE observaciones_planificacion op
                INNER JOIN planificaciones p ON op.planificacion_id = p.id_planificacion
                INNER JOIN unidad u ON p.unidad_id = u.id_unidad
                INNER JOIN asignatura a ON u.asignatura_codigo = a.codigo
                SET op.estado = 'corregido',
                    op.fecha_correccion = NOW()
                WHERE a.docente_codigo = ? 
                AND op.estado = 'corregir'
                AND p.estado = 'A'
            ");

            $result = $stmt->execute([$codigoDocente]);
            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al marcar como corregidas: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar una observación específica como corregida
     */
    public function marcarObservacionCorregida($idObservacion, $codigoDocente) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE observaciones_planificacion op
                INNER JOIN planificaciones p ON op.planificacion_id = p.id_planificacion
                INNER JOIN unidad u ON p.unidad_id = u.id_unidad
                INNER JOIN asignatura a ON u.asignatura_codigo = a.codigo
                SET op.estado = 'corregido',
                    op.fecha_correccion = NOW()
                WHERE op.id_observacion = ? 
                AND a.docente_codigo = ? 
                AND op.estado = 'corregir'
            ");

            return $stmt->execute([$idObservacion, $codigoDocente]);
        } catch (Exception $e) {
            error_log("Error al marcar observación como corregida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener detalles de una observación específica
     */
    public function obtenerObservacion($idObservacion, $codigoDocente) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    op.*,
                    a.nombre_asignatura as asignatura_nombre,
                    u.nombre as unidad_nombre
                FROM observaciones_planificacion op
                INNER JOIN planificaciones p ON op.planificacion_id = p.id_planificacion
                INNER JOIN unidad u ON p.unidad_id = u.id_unidad
                INNER JOIN asignatura a ON u.asignatura_codigo = a.codigo
                WHERE op.id_observacion = ? 
                AND a.docente_codigo = ?
            ");

            $stmt->execute([$idObservacion, $codigoDocente]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? new Notificacion($row) : null;
        } catch (Exception $e) {
            error_log("Error al obtener observación: " . $e->getMessage());
            return null;
        }
    }
}
?>