<?php
class PlanificacionRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getSemana($semana_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM semana WHERE id_semana = ?");
        $stmt->execute([$semana_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUnidad($unidad_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM unidad WHERE id_unidad = ?");
        $stmt->execute([$unidad_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAsignatura($asignatura_codigo)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM asignatura WHERE codigo = ?");
        $stmt->execute([$asignatura_codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAsignaturaConDocente($asignatura_codigo)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, d.nombre AS docente_nombre
            FROM asignatura a
            LEFT JOIN docente d ON a.docente_codigo = d.codigo
            WHERE a.codigo = ?
        ");
        $stmt->execute([$asignatura_codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSemanasPorUnidad($unidad_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM semana WHERE id_unidad = ? ORDER BY fecha_semana ASC");
        $stmt->execute([$unidad_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDocentePorUnidad($unidad_id)
    {
        $stmt = $this->pdo->prepare("
        SELECT d.codigo, d.nombre
        FROM unidad u
        JOIN asignatura a ON u.asignatura_codigo = a.codigo
        JOIN docente d ON a.docente_codigo = d.codigo
        WHERE u.id_unidad = ?
        LIMIT 1
    ");
        $stmt->execute([$unidad_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Devuelve ['codigo' => ..., 'nombre' => ...] o false
    }


    public function getSemanaLinea($id_semana_linea)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM semana_linea WHERE id_semana_linea = ?");
        $stmt->execute([$id_semana_linea]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtiene la unidad asociada a una semana en línea
    public function getUnidadPorSemanaLinea($id_semana_linea)
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*
            FROM semana_linea sl
            JOIN unidad u ON sl.id_unidad = u.id_unidad
            WHERE sl.id_semana_linea = ?
            LIMIT 1
        ");
        $stmt->execute([$id_semana_linea]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtiene la asignatura y docente asociada a una semana en línea
    public function getAsignaturaConDocentePorSemanaLinea($id_semana_linea)
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, d.nombre AS docente_nombre
            FROM semana_linea sl
            JOIN unidad u ON sl.id_unidad = u.id_unidad
            JOIN asignatura a ON u.asignatura_codigo = a.codigo
            LEFT JOIN docente d ON a.docente_codigo = d.codigo
            WHERE sl.id_semana_linea = ?
            LIMIT 1
        ");
        $stmt->execute([$id_semana_linea]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSemanasLineaPorUnidad($id_unidad)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM semana_linea WHERE id_unidad = ? ORDER BY fecha_sabado ASC");
        $stmt->execute([$id_unidad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
