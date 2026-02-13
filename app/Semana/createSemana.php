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

        // --- NUEVO: Verificar si debe copiar datos de semana base ---
        $semana_base = null;
        
        // Verificar datos de la unidad actual
        $stmtCheckBase = $pdo->prepare("SELECT unidad_base, asignatura_codigo FROM unidad WHERE id_unidad = ?");
        $stmtCheckBase->execute([$id_unidad]);
        $unidadActual = $stmtCheckBase->fetch(PDO::FETCH_ASSOC);
        
        if ($unidadActual) {
            // PASO 1: Intentar buscar la ÚLTIMA semana creada en la MISMA unidad
            $stmtUltimaSemana = $pdo->prepare("SELECT * FROM semana 
                                                WHERE id_unidad = ? 
                                                ORDER BY fecha_semana DESC 
                                                LIMIT 1");
            $stmtUltimaSemana->execute([$id_unidad]);
            $semana_base = $stmtUltimaSemana->fetch(PDO::FETCH_ASSOC);
            
            // PASO 2: Si no hay semanas en esta unidad, buscar semana de la Unidad Base
            if (!$semana_base && $unidadActual['unidad_base'] != 1) {
                $asignatura_codigo = $unidadActual['asignatura_codigo'];
                
                // Buscar la unidad base de esta asignatura
                $stmtUnidadBase = $pdo->prepare("SELECT id_unidad FROM unidad 
                                                  WHERE asignatura_codigo = ? 
                                                  AND numero_unidad = 1 
                                                  AND unidad_base = 1 
                                                  LIMIT 1");
                $stmtUnidadBase->execute([$asignatura_codigo]);
                $unidadBase = $stmtUnidadBase->fetch(PDO::FETCH_ASSOC);
                
                if ($unidadBase) {
                    $id_unidad_base = $unidadBase['id_unidad'];
                    
                    // Buscar la primera semana de la unidad base
                    $stmtSemanaBase = $pdo->prepare("SELECT * FROM semana 
                                                     WHERE id_unidad = ? 
                                                     ORDER BY fecha_semana ASC 
                                                     LIMIT 1");
                    $stmtSemanaBase->execute([$id_unidad_base]);
                    $semana_base = $stmtSemanaBase->fetch(PDO::FETCH_ASSOC);
                }
            }
        }
        // --- FIN NUEVO ---

        // Si hay semana base, usar sus datos como valores por defecto
        if ($semana_base) {
            $actividades_previas = $_POST['actividades_previas'] ?? $semana_base['actividades_previas'];
            $tiempo_previas = $_POST['tiempo_previas'] ?? $semana_base['tiempo_actividades_previas'];
            $contenido = $_POST['contenido'] ?? $semana_base['contenido'];
        } else {
            $actividades_previas = $_POST['actividades_previas'] ?? null;
            $tiempo_previas = $_POST['tiempo_previas'] ?? null;
            $contenido = $_POST['contenido'] ?? null;
        }

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $campos = [];
        foreach ($dias as $dia) {
            if ($semana_base) {
                // Usar datos de semana base como valores por defecto
                $campos[$dia] = [
                    'fecha' => nullIfEmpty($_POST['fecha_' . $dia] ?? null),
                    'objetivo' => $_POST['objetivo_' . $dia] ?? $semana_base['objetivo_' . $dia],
                    'innovacion' => $_POST['innovacion_' . $dia] ?? $semana_base['innovacion_' . $dia],
                    'tiempo_objetivo' => $_POST['tiempo_objetivo_' . $dia] ?? $semana_base['tiempo_objetivo_' . $dia],
                    'apertura' => $_POST['apertura_' . $dia] ?? $semana_base['apertura_' . $dia],
                    'tiempo_apertura' => $_POST['tiempo_apertura_' . $dia] ?? $semana_base['tiempo_apertura_' . $dia],
                    'desarrollo' => $_POST['desarrollo_' . $dia] ?? $semana_base['desarrollo_' . $dia],
                    'tiempo_desarrollo' => $_POST['tiempo_desarrollo_' . $dia] ?? $semana_base['tiempo_desarrollo_' . $dia],
                    'cierre' => $_POST['cierre_' . $dia] ?? $semana_base['cierre_' . $dia],
                    'tiempo_cierre' => $_POST['tiempo_cierre_' . $dia] ?? $semana_base['tiempo_cierre_' . $dia],
                    'trabajo_autonomo' => $_POST['trabajo_autonomo_' . $dia] ?? $semana_base['trabajo_autonomo_' . $dia],
                    'fecha_entrega' => nullIfEmpty($_POST['entrega_' . $dia] ?? null),
                ];
            } else {
                // Sin semana base, usar solo POST
                $campos[$dia] = [
                    'fecha' => nullIfEmpty($_POST['fecha_' . $dia] ?? null),
                    'objetivo' => $_POST['objetivo_' . $dia] ?? null,
                    'innovacion' => $_POST['innovacion_' . $dia] ?? null,
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
        }

        $stmt = $pdo->prepare("
            INSERT INTO semana (
                id_unidad, fecha_semana, semana_fin, actividades_previas, tiempo_actividades_previas, contenido,
                fecha_lunes, objetivo_lunes, innovacion_lunes, tiempo_objetivo_lunes, apertura_lunes, tiempo_apertura_lunes, desarrollo_lunes, tiempo_desarrollo_lunes, cierre_lunes, tiempo_cierre_lunes, trabajo_autonomo_lunes, fecha_entrega_lunes,
                fecha_martes, objetivo_martes, innovacion_martes, tiempo_objetivo_martes, apertura_martes, tiempo_apertura_martes, desarrollo_martes, tiempo_desarrollo_martes, cierre_martes, tiempo_cierre_martes, trabajo_autonomo_martes, fecha_entrega_martes,
                fecha_miercoles, objetivo_miercoles, innovacion_miercoles, tiempo_objetivo_miercoles, apertura_miercoles, tiempo_apertura_miercoles, desarrollo_miercoles, tiempo_desarrollo_miercoles, cierre_miercoles, tiempo_cierre_miercoles, trabajo_autonomo_miercoles, fecha_entrega_miercoles,
                fecha_jueves, objetivo_jueves, innovacion_jueves, tiempo_objetivo_jueves, apertura_jueves, tiempo_apertura_jueves, desarrollo_jueves, tiempo_desarrollo_jueves, cierre_jueves, tiempo_cierre_jueves, trabajo_autonomo_jueves, fecha_entrega_jueves,
                fecha_viernes, objetivo_viernes, innovacion_viernes, tiempo_objetivo_viernes, apertura_viernes, tiempo_apertura_viernes, desarrollo_viernes, tiempo_desarrollo_viernes, cierre_viernes, tiempo_cierre_viernes, trabajo_autonomo_viernes, fecha_entrega_viernes
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
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
            $campos['lunes']['innovacion'],
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
            $campos['martes']['innovacion'],
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
            $campos['miercoles']['innovacion'],
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
            $campos['jueves']['innovacion'],
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
            $campos['viernes']['innovacion'],
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