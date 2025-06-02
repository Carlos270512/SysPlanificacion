<?php
require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json');

function nullIfEmpty($value) {
    return (isset($value) && trim($value) !== '') ? $value : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $id_unidad = $_POST['unidad_id'] ?? null;
        $fecha_semana = nullIfEmpty($_POST['semana_inicio'] ?? null);
        $semana_fin = nullIfEmpty($_POST['semana_fin'] ?? null);

        $actividades_previas = $_POST['actividades_previas'] ?? null;
        $tiempo_previas = $_POST['tiempo_previas'] ?? null;
        $contenido = $_POST['contenido'] ?? null;

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $campos = [];
        foreach ($dias as $dia) {
            $campos[$dia] = [
                'fecha' => nullIfEmpty($_POST['fecha_' . $dia] ?? null),
                'objetivo' => $_POST['objetivo_' . $dia] ?? null,
                'tiempo_objetivo' => $_POST['tiempo_objetivo_' . $dia] ?? null,
                'apertura' => $_POST['apertura_' . $dia] ?? null,
                'tiempo_apertura' => $_POST['tiempo_apertura_' . $dia] ?? null,
                'desarrollo' => $_POST['desarrollo_' . $dia] ?? null,
                'tiempo_desarrollo' => $_POST['tiempo_desarrollo_' . $dia] ?? null,
                'cierre' => $_POST['cierre_' . $dia] ?? null,
                'tiempo_cierre' => $_POST['tiempo_cierre_' . $dia] ?? null,
                'trabajo_autonomo' => $_POST['trabajo_autonomo_' . $dia] ?? null,
                'fecha_entrega' => nullIfEmpty($_POST['entrega_' . $dia] ?? null),
            ];
        }

        $stmt = $pdo->prepare("
            INSERT INTO semana (
                id_unidad, fecha_semana, semana_fin, actividades_previas, tiempo_actividades_previas, contenido,
                fecha_lunes, objetivo_lunes, tiempo_objetivo_lunes, apertura_lunes, tiempo_apertura_lunes, desarrollo_lunes, tiempo_desarrollo_lunes, cierre_lunes, tiempo_cierre_lunes, trabajo_autonomo_lunes, fecha_entrega_lunes,
                fecha_martes, objetivo_martes, tiempo_objetivo_martes, apertura_martes, tiempo_apertura_martes, desarrollo_martes, tiempo_desarrollo_martes, cierre_martes, tiempo_cierre_martes, trabajo_autonomo_martes, fecha_entrega_martes,
                fecha_miercoles, objetivo_miercoles, tiempo_objetivo_miercoles, apertura_miercoles, tiempo_apertura_miercoles, desarrollo_miercoles, tiempo_desarrollo_miercoles, cierre_miercoles, tiempo_cierre_miercoles, trabajo_autonomo_miercoles, fecha_entrega_miercoles,
                fecha_jueves, objetivo_jueves, tiempo_objetivo_jueves, apertura_jueves, tiempo_apertura_jueves, desarrollo_jueves, tiempo_desarrollo_jueves, cierre_jueves, tiempo_cierre_jueves, trabajo_autonomo_jueves, fecha_entrega_jueves,
                fecha_viernes, objetivo_viernes, tiempo_objetivo_viernes, apertura_viernes, tiempo_apertura_viernes, desarrollo_viernes, tiempo_desarrollo_viernes, cierre_viernes, tiempo_cierre_viernes, trabajo_autonomo_viernes, fecha_entrega_viernes
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        $params = [
            $id_unidad,
            $fecha_semana,
            $semana_fin,
            $actividades_previas,
            $tiempo_previas,
            $contenido,

            // Lunes
            $campos['lunes']['fecha'],
            $campos['lunes']['objetivo'],
            $campos['lunes']['tiempo_objetivo'],
            $campos['lunes']['apertura'],
            $campos['lunes']['tiempo_apertura'],
            $campos['lunes']['desarrollo'],
            $campos['lunes']['tiempo_desarrollo'],
            $campos['lunes']['cierre'],
            $campos['lunes']['tiempo_cierre'],
            $campos['lunes']['trabajo_autonomo'],
            $campos['lunes']['fecha_entrega'],

            // Martes
            $campos['martes']['fecha'],
            $campos['martes']['objetivo'],
            $campos['martes']['tiempo_objetivo'],
            $campos['martes']['apertura'],
            $campos['martes']['tiempo_apertura'],
            $campos['martes']['desarrollo'],
            $campos['martes']['tiempo_desarrollo'],
            $campos['martes']['cierre'],
            $campos['martes']['tiempo_cierre'],
            $campos['martes']['trabajo_autonomo'],
            $campos['martes']['fecha_entrega'],

            // Miércoles
            $campos['miercoles']['fecha'],
            $campos['miercoles']['objetivo'],
            $campos['miercoles']['tiempo_objetivo'],
            $campos['miercoles']['apertura'],
            $campos['miercoles']['tiempo_apertura'],
            $campos['miercoles']['desarrollo'],
            $campos['miercoles']['tiempo_desarrollo'],
            $campos['miercoles']['cierre'],
            $campos['miercoles']['tiempo_cierre'],
            $campos['miercoles']['trabajo_autonomo'],
            $campos['miercoles']['fecha_entrega'],

            // Jueves
            $campos['jueves']['fecha'],
            $campos['jueves']['objetivo'],
            $campos['jueves']['tiempo_objetivo'],
            $campos['jueves']['apertura'],
            $campos['jueves']['tiempo_apertura'],
            $campos['jueves']['desarrollo'],
            $campos['jueves']['tiempo_desarrollo'],
            $campos['jueves']['cierre'],
            $campos['jueves']['tiempo_cierre'],
            $campos['jueves']['trabajo_autonomo'],
            $campos['jueves']['fecha_entrega'],

            // Viernes
            $campos['viernes']['fecha'],
            $campos['viernes']['objetivo'],
            $campos['viernes']['tiempo_objetivo'],
            $campos['viernes']['apertura'],
            $campos['viernes']['tiempo_apertura'],
            $campos['viernes']['desarrollo'],
            $campos['viernes']['tiempo_desarrollo'],
            $campos['viernes']['cierre'],
            $campos['viernes']['tiempo_cierre'],
            $campos['viernes']['trabajo_autonomo'],
            $campos['viernes']['fecha_entrega'],
        ];

        $stmt->execute($params);

        $semana_id = $pdo->lastInsertId();

        $pdo->commit();
        echo json_encode(['success' => true, 'semana_id' => $semana_id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
echo json_encode(['success' => false, 'message' => 'Método no permitido']);