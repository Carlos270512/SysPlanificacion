<?php

class GestionPlanificacionesRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtener todos los docentes activos
     */
    public function obtenerDocentesActivos()
    {
        try {
            $stmt = $this->pdo->query("SELECT codigo, nombre FROM docente WHERE estado = 'ACTIVO' ORDER BY nombre");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener docentes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener asignaturas por código de docente
     */
    public function obtenerAsignaturasPorDocente($codigoDocente)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM asignatura WHERE docente_codigo = ?");
            $stmt->execute([$codigoDocente]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener asignaturas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener docente por código
     */
    public function obtenerDocentePorCodigo($codigoDocente)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT codigo, nombre FROM docente WHERE codigo = ? AND estado = 'ACTIVO'");
            $stmt->execute([$codigoDocente]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener docente: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener asignatura por código
     */
    public function obtenerAsignaturaPorCodigo($codigoAsignatura)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM asignatura WHERE codigo = ?");
            $stmt->execute([$codigoAsignatura]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener asignatura: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener planificaciones por asignatura
     */
    public function obtenerPlanificacionesPorAsignatura($codigoAsignatura)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, u.numero_unidad, u.nombre_unidad 
                FROM planificaciones p 
                INNER JOIN unidad u ON p.unidad_id = u.id_unidad 
                INNER JOIN asignatura a ON u.asignatura_codigo = a.codigo 
                WHERE a.codigo = ? AND p.estado = 'A'
                ORDER BY u.numero_unidad
            ");
            $stmt->execute([$codigoAsignatura]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener planificaciones: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerUnidadesConPlanificacionesPorAsignatura($codigoAsignatura)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.id_unidad,
                    u.numero_unidad,
                    u.nombre as nombre_unidad,
                    u.semana_inicio,
                    u.semana_fin,
                    p.id_planificacion,
                    p.nombre_archivo,
                    p.fecha_creacion,
                    p.fecha_actualizacion,
                    p.usuario_creacion,
                    p.usuario_actualizacion
                FROM unidad u
                LEFT JOIN planificaciones p ON u.id_unidad = p.unidad_id AND p.estado = 'A'
                WHERE u.asignatura_codigo = ?
                ORDER BY u.numero_unidad ASC
            ");
            $stmt->execute([$codigoAsignatura]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener unidades con planificaciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener solo las planificaciones activas de una asignatura específica
     */
    public function obtenerPlanificacionesActivasPorAsignatura($codigoAsignatura)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.id_planificacion,
                    p.unidad_id,
                    p.nombre_archivo,
                    p.fecha_creacion,
                    p.fecha_actualizacion,
                    p.usuario_creacion,
                    p.usuario_actualizacion,
                    u.numero_unidad,
                    u.nombre as nombre_unidad
                FROM planificaciones p
                INNER JOIN unidad u ON p.unidad_id = u.id_unidad
                WHERE u.asignatura_codigo = ? AND p.estado = 'A'
                ORDER BY u.numero_unidad ASC
            ");
            $stmt->execute([$codigoAsignatura]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener planificaciones activas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Eliminar planificación (cambiar estado a 'E')
     */
    public function eliminarPlanificacion($idPlanificacion, $usuarioActualizacion)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE planificaciones 
                SET estado = 'E', 
                    usuario_actualizacion = ?,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_planificacion = ?
            ");
            return $stmt->execute([$usuarioActualizacion, $idPlanificacion]);
        } catch (PDOException $e) {
            error_log("Error al eliminar planificación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener archivo PDF de planificación
     */
    public function obtenerArchivoPlanificacion($idPlanificacion)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT archivo_pdf, tipo_mime, nombre_archivo
                FROM planificaciones 
                WHERE id_planificacion = ? AND estado = 'A'
            ");
            $stmt->execute([$idPlanificacion]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener archivo: " . $e->getMessage());
            return null;
        }
    }
}
