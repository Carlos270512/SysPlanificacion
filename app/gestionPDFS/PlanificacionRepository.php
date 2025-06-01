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
}
