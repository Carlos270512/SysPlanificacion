<?php

class CoevaluacionRepository {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Obtener datos del docente coordinador por código
     */
    public function getDocentePorCodigo($codigo) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT codigo, nombre, titulo, carrera, rol, correo, fecha_ingreso 
                FROM docente 
                WHERE codigo = ? AND estado = 'ACTIVO'
            ");
            $stmt->execute([$codigo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Agregar título abreviado
            if ($result) {
                $result['titulo_abreviado'] = $this->obtenerTituloAbreviado($result['titulo']);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error en getDocentePorCodigo: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener título abreviado
     */
    private function obtenerTituloAbreviado($titulo) {
        $titulo = strtolower(trim($titulo));
        
        if (strpos($titulo, 'magister') !== false || strpos($titulo, 'maestr') !== false) {
            return 'Mg.';
        } elseif (strpos($titulo, 'ingenier') !== false) {
            return 'Ing.';
        } elseif (strpos($titulo, 'doctor') !== false || strpos($titulo, 'phd') !== false) {
            return 'PhD.';
        } elseif (strpos($titulo, 'licenciad') !== false) {
            return 'Lic.';
        } elseif (strpos($titulo, 'arquitect') !== false) {
            return 'Arq.';
        } else {
            return 'Sr./Sra.';
        }
    }
    
    /**
     * Obtener todos los docentes de una carrera específica
     */
    public function getDocentesPorCarrera($carrera) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT codigo, nombre, titulo, rol, correo 
                FROM docente 
                WHERE carrera = ? AND estado = 'ACTIVO'
                ORDER BY nombre ASC
            ");
            $stmt->execute([$carrera]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getDocentesPorCarrera: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener todas las carreras disponibles
     */
    public function getCarreras() {
        try {
            $stmt = $this->conexion->prepare("
                SELECT DISTINCT carrera 
                FROM docente 
                WHERE estado = 'ACTIVO' 
                ORDER BY carrera ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getCarreras: " . $e->getMessage());
            return [];
        }
    }
}
?>