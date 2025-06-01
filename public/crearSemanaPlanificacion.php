<?php
require_once __DIR__ . '/../config/conexion.php';
$id_unidad = isset($_GET['id_unidad']) ? intval($_GET['id_unidad']) : null;
$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$nombre_unidad = '';
if ($id_unidad) {
    $stmt = $pdo->prepare("SELECT nombre FROM unidad WHERE id_unidad = ?");
    $stmt->execute([$id_unidad]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombre_unidad = $row ? $row['nombre'] : '';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Semana Planificación</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
    <style>
        .tabla-semana th,
        .tabla-semana td {
            border: 1px solid #000;
            padding: 4px;
        }

        .tabla-semana {
            border-collapse: collapse;
            width: 100%;
        }

        .resaltado {
            background: #ffff99;
            font-weight: bold;
        }

        textarea {
            width: 100%;
            min-height: 40px;
        }

        input[type="text"],
        input[type="date"] {
            width: 100%;
        }

        .center {
            text-align: center;
        }

        .quill-editor {
            min-height: 60px;
            background: #fff;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2>Planificación semanal</h2>
        <?php if ($nombre_unidad): ?>
            <div class="alert alert-info mb-3">
                <strong>Unidad:</strong> <?php echo htmlspecialchars($nombre_unidad); ?>
            </div>
        <?php endif; ?>
        <div id="msgSemana"></div>
        <form id="formSemana" method="post" action="/SysPlanificacion/app/Semana/createSemana.php">
            <input type="hidden" name="unidad_id" value="<?php echo htmlspecialchars($id_unidad); ?>">
            <div class="mb-2">
                <label class="resaltado">Semana: </label>
                <input type="date" name="semana_inicio" required>
                <span class="ms-2">al</span>
                <input type="date" name="semana_fin" disabled>
            </div>
            <div class="mb-2">
                <label class="resaltado">Actividades previas a la clase:</label>
                <div id="editor_actividades_previas" class="quill-editor"></div>
                <input type="hidden" name="actividades_previas" required>
                <label class="ms-2">Tiempo:</label>
                <input type="text" name="tiempo_previas" style="width:80px;" placeholder="min">
            </div>
            <div class="mb-2">
                <label class="resaltado">Contenido:</label>
                <div id="editor_contenido" class="quill-editor"></div>
                <input type="hidden" name="contenido" required>
            </div>
            <table class="tabla-semana">
                <tr>
                    <th rowspan="2" class="center">Día</th>
                    <th colspan="2" class="center">Objetivo</th>
                    <th colspan="2" class="center">Apertura</th>
                    <th colspan="2" class="center">Desarrollo</th>
                    <th colspan="2" class="center">Cierre</th>
                    <th class="center">Trabajo Autónomo</th>
                    <th class="center">Entrega</th>
                </tr>
                <tr>
                    <th class="center">Descripción</th>
                    <th class="center">Tiempo</th>
                    <th class="center">Descripción</th>
                    <th class="center">Tiempo</th>
                    <th class="center">Descripción</th>
                    <th class="center">Tiempo</th>
                    <th class="center">Descripción</th>
                    <th class="center">Tiempo</th>
                    <th class="center">Descripción</th>
                    <th class="center">Fecha</th>
                </tr>
                <?php
                $dias = [
                    'lunes' => 'Lunes',
                    'martes' => 'Martes',
                    'miercoles' => 'Miércoles',
                    'jueves' => 'Jueves',
                    'viernes' => 'Viernes'
                ];
                $campos = ['objetivo', 'apertura', 'desarrollo', 'cierre', 'trabajo_autonomo'];
                foreach ($dias as $dia_key => $dia_nombre): ?>
                    <tr>
                        <td class="center resaltado" id="th_<?php echo $dia_key; ?>" data-nombre="<?php echo $dia_nombre; ?>">
                            <?php echo $dia_nombre; ?>
                        </td>
                        <?php foreach ($campos as $campo): ?>
                            <?php if ($campo === 'trabajo_autonomo'): ?>
                                <td>
                                    <div id="editor_<?php echo $campo . '_' . $dia_key; ?>" class="quill-editor"></div>
                                    <input type="hidden" name="<?php echo $campo . '_' . $dia_key; ?>">
                                </td>
                            <?php else: ?>
                                <td>
                                    <div id="editor_<?php echo $campo . '_' . $dia_key; ?>" class="quill-editor"></div>
                                    <input type="hidden" name="<?php echo $campo . '_' . $dia_key; ?>">
                                </td>
                                <td>
                                    <input type="text" name="tiempo_<?php echo $campo . '_' . $dia_key; ?>" style="width:60px;">
                                </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <td><input type="date" name="entrega_<?php echo $dia_key; ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-primary" id="btnGuardarSemana">Guardar Semana</button>
                <button type="button" class="btn btn-success ms-2" id="btnVisualizarPDF" disabled>Visualizar PDF</button>
                <a href="crearPlanificaciones.php?codigo=<?php echo urlencode($codigo); ?>&volver=1&id_unidad=<?php echo urlencode($id_unidad); ?>" class="btn btn-secondary ms-2">
                    &larr; Atrás
                </a>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script src="/SysPlanificacion/public/assets/js/pdfBuilder.js"></script>
    <script src="/SysPlanificacion/public/assets/js/crearSemana.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            const params = new URLSearchParams(window.location.search);
            const idUnidad = params.get('id_unidad');
            if (!idUnidad) return;

            // Espera a que Quill esté inicializado
            function waitForQuillEditors(callback) {
                let tries = 0;

                function check() {
                    if (window.quill_editors && Object.keys(window.quill_editors).length > 0) {
                        callback();
                    } else if (++tries < 20) {
                        setTimeout(check, 100);
                    }
                }
                check();
            }

            // Trae los datos de la semana si existen
            const resp = await fetch(`/SysPlanificacion/app/Semana/getSemana.php?id_unidad=${idUnidad}`);
            const data = await resp.json();
            if (data.success && data.semana) {
                const semana = data.semana;
                if (semana.fecha_semana) document.querySelector('input[name="semana_inicio"]').value = semana.fecha_semana;
                if (semana.semana_fin) document.querySelector('input[name="semana_fin"]').value = semana.semana_fin; // <-- Agrega esta línea
                if (semana.tiempo_actividades_previas) document.querySelector('input[name="tiempo_previas"]').value = semana.tiempo_actividades_previas;

                waitForQuillEditors(() => {
                    // Carga y sincroniza los campos Quill y sus inputs hidden
                    if (semana.actividades_previas) {
                        window.quill_editors['actividades_previas'].root.innerHTML = semana.actividades_previas;
                        const input = document.querySelector('input[name="actividades_previas"]');
                        if (input) input.value = semana.actividades_previas;
                    }
                    if (semana.contenido) {
                        window.quill_editors['contenido'].root.innerHTML = semana.contenido;
                        const input = document.querySelector('input[name="contenido"]');
                        if (input) input.value = semana.contenido;
                    }
                    const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
                    const campos = ['objetivo', 'apertura', 'desarrollo', 'cierre', 'trabajo_autonomo'];
                    dias.forEach(dia => {
                        campos.forEach(campo => {
                            const key = `${campo}_${dia}`;
                            if (semana[key] && window.quill_editors[key]) {
                                window.quill_editors[key].root.innerHTML = semana[key];
                                const input = document.querySelector(`input[name="${key}"]`);
                                if (input) input.value = semana[key];
                            }
                        });
                    });
                });

                // Carga los campos de tiempo y fecha de entrega
                const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
                dias.forEach(dia => {
                    if (semana[`tiempo_objetivo_${dia}`]) document.querySelector(`input[name="tiempo_objetivo_${dia}"]`).value = semana[`tiempo_objetivo_${dia}`] || '';
                    if (semana[`tiempo_apertura_${dia}`]) document.querySelector(`input[name="tiempo_apertura_${dia}"]`).value = semana[`tiempo_apertura_${dia}`] || '';
                    if (semana[`tiempo_desarrollo_${dia}`]) document.querySelector(`input[name="tiempo_desarrollo_${dia}"]`).value = semana[`tiempo_desarrollo_${dia}`] || '';
                    if (semana[`tiempo_cierre_${dia}`]) document.querySelector(`input[name="tiempo_cierre_${dia}"]`).value = semana[`tiempo_cierre_${dia}`] || '';
                    if (semana[`fecha_entrega_${dia}`]) document.querySelector(`input[name="entrega_${dia}"]`).value = semana[`fecha_entrega_${dia}`] || '';
                });
                // Deshabilita el botón guardar si ya existe
                const btnGuardar = document.getElementById('btnGuardarSemana');
                if (btnGuardar) btnGuardar.style.display = 'none';
                document.getElementById('btnVisualizarPDF').disabled = false;
                // Guarda el id de la semana para auto-save
                window.idSemanaGuardada = semana.id_semana;
            }
        });
    </script>

</html>