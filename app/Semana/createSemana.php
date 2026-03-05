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

        // --- NUEVO: Verificar si debe copiar TODAS las semanas de la unidad base ---
        $semanas_base = [];
        
        // Verificar datos de la unidad actual
        $stmtCheckBase = $pdo->prepare("SELECT unidad_base, asignatura_codigo FROM unidad WHERE id_unidad = ?");
        $stmtCheckBase->execute([$id_unidad]);
        $unidadActual = $stmtCheckBase->fetch(PDO::FETCH_ASSOC);
        
        if ($unidadActual) {
            // PASO 1: Verificar si ya existen semanas en la unidad actual
            $stmtExistentes = $pdo->prepare("SELECT COUNT(*) as total FROM semana WHERE id_unidad = ?");
            $stmtExistentes->execute([$id_unidad]);
            $existentes = $stmtExistentes->fetch(PDO::FETCH_ASSOC);
            
            // PASO 2: Si NO hay semanas Y no es unidad base, copiar TODAS las semanas de la unidad base
            if ($existentes['total'] == 0 && $unidadActual['unidad_base'] != 1) {
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
                    
                    // Buscar TODAS las semanas de la unidad base ordenadas por fecha
                    $stmtSemanasBase = $pdo->prepare("SELECT * FROM semana 
                                                      WHERE id_unidad = ? 
                                                      ORDER BY fecha_semana ASC");
                    $stmtSemanasBase->execute([$id_unidad_base]);
                    $semanas_base = $stmtSemanasBase->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        }
        // --- FIN NUEVO ---

        // ========== CREAR MÚLTIPLES SEMANAS DESDE LA BASE ==========
        if (!empty($semanas_base)) {
            // Hay semanas en la unidad base, crear TODAS automáticamente
            $semanas_creadas = [];
            
            foreach ($semanas_base as $semana_base) {
                $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
                
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
                    $semana_base['fecha_semana'],
                    $semana_base['semana_fin'],
                    $semana_base['actividades_previas'],
                    $semana_base['tiempo_actividades_previas'],
                    $semana_base['contenido'],

                    // Lunes
                    $semana_base['fecha_lunes'],
                    $semana_base['objetivo_lunes'],
                    $semana_base['innovacion_lunes'],
                    $semana_base['tiempo_objetivo_lunes'],
                    $semana_base['apertura_lunes'],
                    $semana_base['tiempo_apertura_lunes'],
                    $semana_base['desarrollo_lunes'],
                    $semana_base['tiempo_desarrollo_lunes'],
                    $semana_base['cierre_lunes'],
                    $semana_base['tiempo_cierre_lunes'],
                    $semana_base['trabajo_autonomo_lunes'],
                    $semana_base['fecha_entrega_lunes'],

                    // Martes
                    $semana_base['fecha_martes'],
                    $semana_base['objetivo_martes'],
                    $semana_base['innovacion_martes'],
                    $semana_base['tiempo_objetivo_martes'],
                    $semana_base['apertura_martes'],
                    $semana_base['tiempo_apertura_martes'],
                    $semana_base['desarrollo_martes'],
                    $semana_base['tiempo_desarrollo_martes'],
                    $semana_base['cierre_martes'],
                    $semana_base['tiempo_cierre_martes'],
                    $semana_base['trabajo_autonomo_martes'],
                    $semana_base['fecha_entrega_martes'],

                    // Miércoles
                    $semana_base['fecha_miercoles'],
                    $semana_base['objetivo_miercoles'],
                    $semana_base['innovacion_miercoles'],
                    $semana_base['tiempo_objetivo_miercoles'],
                    $semana_base['apertura_miercoles'],
                    $semana_base['tiempo_apertura_miercoles'],
                    $semana_base['desarrollo_miercoles'],
                    $semana_base['tiempo_desarrollo_miercoles'],
                    $semana_base['cierre_miercoles'],
                    $semana_base['tiempo_cierre_miercoles'],
                    $semana_base['trabajo_autonomo_miercoles'],
                    $semana_base['fecha_entrega_miercoles'],

                    // Jueves
                    $semana_base['fecha_jueves'],
                    $semana_base['objetivo_jueves'],
                    $semana_base['innovacion_jueves'],
                    $semana_base['tiempo_objetivo_jueves'],
                    $semana_base['apertura_jueves'],
                    $semana_base['tiempo_apertura_jueves'],
                    $semana_base['desarrollo_jueves'],
                    $semana_base['tiempo_desarrollo_jueves'],
                    $semana_base['cierre_jueves'],
                    $semana_base['tiempo_cierre_jueves'],
                    $semana_base['trabajo_autonomo_jueves'],
                    $semana_base['fecha_entrega_jueves'],

                    // Viernes
                    $semana_base['fecha_viernes'],
                    $semana_base['objetivo_viernes'],
                    $semana_base['innovacion_viernes'],
                    $semana_base['tiempo_objetivo_viernes'],
                    $semana_base['apertura_viernes'],
                    $semana_base['tiempo_apertura_viernes'],
                    $semana_base['desarrollo_viernes'],
                    $semana_base['tiempo_desarrollo_viernes'],
                    $semana_base['cierre_viernes'],
                    $semana_base['tiempo_cierre_viernes'],
                    $semana_base['trabajo_autonomo_viernes'],
                    $semana_base['fecha_entrega_viernes'],
                ];

                $stmt->execute($params);
                $semanas_creadas[] = $pdo->lastInsertId();
            }

            $pdo->commit();
            echo json_encode([
                'success' => true, 
                'message' => 'Se crearon ' . count($semanas_creadas) . ' semanas automáticamente desde la unidad base',
                'semanas_ids' => $semanas_creadas,
                'total_semanas' => count($semanas_creadas)
            ]);
            exit;
        }
        // ========== FIN CREAR MÚLTIPLES SEMANAS ==========

        // Si no hay semanas base, crear normalmente
        $actividades_previas = $_POST['actividades_previas'] ?? null;
        $tiempo_previas = $_POST['tiempo_previas'] ?? null;
        $contenido = $_POST['contenido'] ?? null;

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $campos = [];
        foreach ($dias as $dia) {
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