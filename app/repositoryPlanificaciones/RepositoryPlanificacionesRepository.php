<?php

class RepositoryPlanificacionesRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtener todas las planificaciones del repositorio (sin filtros de usuario)
     */
    public function obtenerTodasLasPlanificaciones()
    {
        try {
            $sql = "SELECT 
                        id_repository,
                        nombre_archivo,
                        tipo_mime,
                        asignatura,
                        periodo_lectivo,
                        fecha_creacion,
                        usuario_creacion
                    FROM planificaciones_repository 
                    ORDER BY fecha_creacion DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener planificaciones del repositorio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener planificación por ID para visualización
     */
    public function obtenerPlanificacionPorId($id)
    {
        try {
            $sql = "SELECT * FROM planificaciones_repository WHERE id_repository = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener planificación por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener planificaciones filtradas por criterios (búsqueda manual)
     */
    public function obtenerPlanificacionesFiltradas($asignatura = null, $periodo = null)
    {
        try {
            $sql = "SELECT 
                        id_repository,
                        nombre_archivo,
                        tipo_mime,
                        asignatura,
                        periodo_lectivo,
                        fecha_creacion,
                        usuario_creacion
                    FROM planificaciones_repository WHERE 1=1";
            
            $params = [];
            
            if ($asignatura && trim($asignatura) !== '') {
                $sql .= " AND asignatura LIKE :asignatura";
                $params[':asignatura'] = "%" . trim($asignatura) . "%";
            }
            
            if ($periodo && trim($periodo) !== '') {
                $sql .= " AND periodo_lectivo LIKE :periodo";
                $params[':periodo'] = "%" . trim($periodo) . "%";
            }
            
            $sql .= " ORDER BY fecha_creacion DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener planificaciones filtradas: " . $e->getMessage());
            return [];
        }
    }
}