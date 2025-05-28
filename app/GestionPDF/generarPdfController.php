<?php
require_once __DIR__ . '/../../config/conexion.php';

class GenerarPdfController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerDatosPorSemana($semana_id) {
        $datos = [
            'semana' => null,
            'unidad' => null,
            'asignatura' => null,
            'docente' => null
        ];

        // Buscar semana
        $stmt = $this->pdo->prepare("SELECT * FROM semana WHERE id_semana = ?");
        $stmt->execute([$semana_id]);
        $semana = $stmt->fetch(PDO::FETCH_ASSOC);
        $datos['semana'] = $semana;

        if ($semana && $semana['id_unidad']) {
            // Buscar unidad
            $stmt = $this->pdo->prepare("SELECT * FROM unidad WHERE id_unidad = ?");
            $stmt->execute([$semana['id_unidad']]);
            $unidad = $stmt->fetch(PDO::FETCH_ASSOC);
            $datos['unidad'] = $unidad;

            if ($unidad && $unidad['asignatura_codigo']) {
                // Buscar asignatura
                $stmt = $this->pdo->prepare("SELECT * FROM asignatura WHERE codigo = ?");
                $stmt->execute([$unidad['asignatura_codigo']]);
                $asignatura = $stmt->fetch(PDO::FETCH_ASSOC);
                $datos['asignatura'] = $asignatura;

                if ($asignatura && $asignatura['docente_codigo']) {
                    // Buscar docente
                    $stmt = $this->pdo->prepare("SELECT nombre, carrera FROM docente WHERE codigo = ?");
                    $stmt->execute([$asignatura['docente_codigo']]);
                    $docente = $stmt->fetch(PDO::FETCH_ASSOC);
                    $datos['docente'] = $docente;
                }
            }
        }
        return $datos;

    }

    public static function obtenerModalidadPorJornada($jornada) {
        switch (strtoupper($jornada)) {
            case 'PM':
                return 'Presencial';
            case 'HM':
            case 'HN':
                return 'Híbrida';
            case 'V':
                return 'Virtual';
            case 'PN':
                return 'Presencial Nocturna';
            case 'S':
                return 'Sábado';
            default:
                return 'Desconocida';
        }
    }
}