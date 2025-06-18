<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/../config/conexion.php';

$codigo = $_GET['codigo'] ?? '';
if (!$codigo) {
    echo "Código de asignatura no proporcionado.";
    exit();
}

// Consulta la asignatura y el docente
$stmt = $pdo->prepare("SELECT a.*, d.nombre as docente_nombre FROM asignatura a
    LEFT JOIN docente d ON a.docente_codigo = d.codigo
    WHERE a.codigo = ?");
$stmt->execute([$codigo]);
$asig = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asig) {
    echo "Asignatura no encontrada.";
    exit();
}

// Calcular el siguiente número de unidad
$stmtUnidad = $pdo->prepare("SELECT COUNT(*) as total FROM unidad WHERE asignatura_codigo = ?");
$stmtUnidad->execute([$codigo]);
$rowUnidad = $stmtUnidad->fetch(PDO::FETCH_ASSOC);
$siguiente_numero_unidad = intval($rowUnidad['total']) + 1;

// Lógica de modalidad según jornada
$jornada = strtoupper($asig['jornada']);
switch ($jornada) {
    case 'PM':
    case 'PN':
        $modalidad = 'PRESENCIAL';
        break;
    case 'HM':
    case 'HN':
        $modalidad = 'HIBRIDA';
        break;
    case 'V':
        $modalidad = 'VIRTUAL';
        break;
    case 'S':
        $modalidad = 'SABADOS';
        break;
    case 'PL':
    case 'EL': // <-- Agrega esta línea
        $modalidad = 'EN LINEA';
        break;
    default:
        $modalidad = 'NO DEFINIDA';
        break;
}
// Cambia aquí también:
$soloCamposPL = ($jornada === 'PL' || $jornada === 'EL');

// --- NUEVO: Cargar datos de la unidad si viene id_unidad ---
$id_unidad = isset($_GET['id_unidad']) ? intval($_GET['id_unidad']) : null;
$unidad = null;
if ($id_unidad) {
    $stmtUnidadData = $pdo->prepare("SELECT * FROM unidad WHERE id_unidad = ?");
    $stmtUnidadData->execute([$id_unidad]);
    $unidad = $stmtUnidadData->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Planificación de Clase</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/crearPlanificacionestyle.css">
</head>

<body>
    <div class="container mt-4">
        <!-- Cabecera de la planificación -->
        <table class="tabla-planificacion">
            <tr>
                <th colspan="6" style="text-align:center;">PLANIFICACION DE CLASE</th>
            </tr>
            <tr>
                <td class="resaltado">Asignatura:</td>
                <td><?php echo htmlspecialchars($asig['nombre_asignatura']); ?></td>
                <td class="resaltado">Código de la asignatura:</td>
                <td><?php echo htmlspecialchars($asig['codigo']); ?></td>
                <td class="resaltado">Modalidad:</td>
                <td><?php echo $modalidad; ?></td>
            </tr>
            <tr>
                <td class="resaltado">Nivel:</td>
                <td><?php echo htmlspecialchars($asig['nivel']); ?></td>
                <td class="resaltado">Jornada:</td>
                <td><?php echo htmlspecialchars($asig['jornada']); ?></td>
                <td class="resaltado">Docente:</td>
                <td><?php echo htmlspecialchars($asig['docente_nombre']); ?></td>
            </tr>
        </table>
        <br>
        <!-- Mensajes del formulario -->
        <div id="msgUnidad"></div>
        <!-- Formulario para crear unidad -->
        <form id="formUnidad" class="mt-4" method="post" action="/SysPlanificacion/app/Unidad/createUnidad.php">
            <input type="hidden" name="asignatura_codigo" value="<?php echo htmlspecialchars($asig['codigo']); ?>">
            <table class="tabla-planificacion">
                <tr>
                    <td colspan="6" style="text-align:left;">
                        <span class="resaltado">Unidad N°</span>
                        <input type="number" name="numero_unidad" min="1" required style="width:60px; text-align:center;" class="subrayado ms-2 me-4"
                            value="<?php echo $unidad ? htmlspecialchars($unidad['numero_unidad']) : $siguiente_numero_unidad; ?>">
                    </td>
                </tr>
                <tr>
                    <td colspan="6">
                        <span class="resaltado">Nombre:</span>
                        <input type="text" name="nombre" required class="subrayado ms-2" style="min-width:200px;"
                            value="<?php echo $unidad ? htmlspecialchars($unidad['nombre']) : ''; ?>">
                    </td>
                </tr>
                <?php if ($soloCamposPL): ?>
                    <tr>
                        <!-- Columna izquierda: Objetivo de la unidad y Bibliografía debajo -->
                        <td rowspan="2" colspan="3" style="vertical-align:top; min-width:350px;">
                            <div class="resaltado">Objetivo de la unidad:</div>
                            <div id="editor_objetivo_unidad" class="quill-editor"></div>
                            <textarea name="objetivo_unidad" id="objetivo_unidad" class="d-none"><?php echo $unidad ? htmlspecialchars($unidad['objetivo_unidad']) : ''; ?></textarea>
                            <div class="resaltado mt-3">Bibliografía:</div>
                            <div id="editor_bibliografia" class="quill-editor"></div>
                            <textarea name="bibliografia" id="bibliografia" class="d-none"><?php echo $unidad ? htmlspecialchars($unidad['bibliografia']) : ''; ?></textarea>
                        </td>
                        <!-- Columna derecha arriba: Equipo/Herramienta/Recursos didácticos -->
                        <td colspan="3" style="vertical-align:top;">
                            <div class="resaltado">Equipo/Herramienta/<br>Recursos didácticos de la unidad:</div>
                            <div id="editor_recursos_didacticos" class="quill-editor"></div>
                            <textarea name="recursos_didacticos" id="recursos_didacticos" class="d-none"><?php echo $unidad ? htmlspecialchars($unidad['recursos_didacticos']) : ''; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <!-- Columna derecha abajo: Estrategia de enseñanza y aprendizaje -->
                        <td colspan="3" style="vertical-align:top;">
                            <div class="resaltado">Estrategia de enseñanza y aprendizaje:</div>
                            <div id="editor_estrategia" class="quill-editor"></div>
                            <textarea name="estrategia" id="estrategia" class="d-none"><?php echo $unidad ? htmlspecialchars($unidad['estrategia_ensenanza_aprendizaje'] ?? '') : ''; ?></textarea>
                        </td>
                    </tr>
                <?php else: ?>
                    <!-- Aquí pon los campos normales para otras jornadas -->
                    <tr>
                        <td colspan="2" style="vertical-align:top;">
                            <div class="resaltado">Objetivo de la unidad:</div>
                            <div id="editor_objetivo_unidad" class="quill-editor"></div>
                            <textarea name="objetivo_unidad" id="objetivo_unidad" class="d-none"><?php echo $unidad ? htmlspecialchars($unidad['objetivo_unidad']) : ''; ?></textarea>
                            <div class="resaltado mt-2">Bibliografía:</div>
                            <div id="editor_bibliografia" class="quill-editor"></div>
                            <textarea name="bibliografia" id="bibliografia" class="d-none"><?php echo $unidad ? htmlspecialchars($unidad['bibliografia']) : ''; ?></textarea>

                        </td>
                        <td style="vertical-align:top;">
                            <div class="resaltado mt-2">Metodología:</div>
                            <div id="editor_metodologia" class="quill-editor"></div>
                            <textarea name="metodologia" id="metodologia" class="d-none"><?php echo $unidad ? htmlspecialchars($unidad['metodologia']) : ''; ?></textarea>
                        </td>
                        <td style="vertical-align:top;">
                            <div class="resaltado mt-2">Actividades de recuperación:</div>
                            <div id="editor_actividades_recuperacion" class="quill-editor"></div>
                            <textarea name="actividades_recuperacion" id="actividades_recuperacion" class="d-none"><?php echo $unidad ? htmlspecialchars($unidad['actividades_recuperacion']) : ''; ?></textarea>
                        <td colspan="3" style="vertical-align:top;">
                            <div class="resaltado">Equipo/Herramienta/<br>Recursos didácticos de la unidad:</div>
                            <div id="editor_recursos_didacticos" class="quill-editor"></div>
                            <textarea name="recursos_didacticos" id="recursos_didacticos" class="d-none"><?php echo $unidad ? htmlspecialchars($unidad['recursos_didacticos']) : ''; ?></textarea>

                        </td>
                    </tr>
                <?php endif; ?>
                <input type="hidden" name="id_unidad" id="id_unidad" value="<?php echo $unidad ? htmlspecialchars($unidad['id_unidad']) : ''; ?>">
                </tr>
            </table>
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <!-- Botón Atrás alineado a la izquierda pero con margen a la derecha -->
                <button type="button" class="btn btn-secondary btn-atras-custom" data-bs-toggle="modal" data-bs-target="#modalConfirmarAtras">
                    &larr; Atrás
                </button>
                <div>
                    <?php if (!$unidad): ?>
                        <button type="submit" id="btnGuardarSemana" class="btn btn-primary">
                            Guardar Unidad
                        </button>
                    <?php endif; ?>
                    <button type="button" id="btnNuevaSemana" class="btn  ms-2 btn-nueva-semana-custom" <?php echo ($unidad ? '' : 'disabled'); ?>>
                        Nueva semana
                    </button>
                </div>
            </div>
        </form>

    </div>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="modalConfirmarAtras" tabindex="-1" aria-labelledby="modalConfirmarAtrasLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmarAtrasLabel">¿Está seguro de regresar?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    Si regresa perderá los cambios realizados en la planificación.<br>¿Desea continuar?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="planificaciones.php" class="btn btn-danger">Sí, regresar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script>
        document.getElementById('btnNuevaSemana')?.addEventListener('click', function() {
            const idUnidad = document.getElementById('id_unidad').value;
            const codigo = "<?php echo htmlspecialchars($asig['codigo']); ?>";
            window.location.href = `crearSemanaPlanificacion.php?id_unidad=${idUnidad}&codigo=${codigo}`;
        });
    </script>
    <script>
        // Inicializa Quill en los campos requeridos y sincroniza con los textarea ocultos
        const quillFields = [{
                id: 'editor_objetivo_unidad',
                name: 'objetivo_unidad'
            },
            {
                id: 'editor_bibliografia',
                name: 'bibliografia'
            },
            {
                id: 'editor_metodologia',
                name: 'metodologia'
            },
            {
                id: 'editor_actividades_recuperacion',
                name: 'actividades_recuperacion'
            },
            {
                id: 'editor_recursos_didacticos',
                name: 'recursos_didacticos'
            },
            {
                id: 'editor_estrategia',
                name: 'estrategia'
            } // <-- Nuevo campo

        ];
        const quillEditors = {};

        quillFields.forEach(field => {
            if (!document.getElementById(field.id)) return;
            const quill = new Quill('#' + field.id, {
                theme: 'snow',
                placeholder: 'Escribe aquí...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        ['link'],
                        ['clean']
                    ]
                }
            });
            quillEditors[field.name] = quill;

            // Sincroniza el contenido con el textarea oculto
            quill.on('text-change', function() {
                document.getElementById(field.name).value = quill.root.innerHTML;
            });

            // Autosave al perder foco
            quill.root.addEventListener('blur', function() {
                document.getElementById(field.name).dispatchEvent(new Event('blur'));
            });

            // --- NUEVO: Cargar datos en Quill si existen valores en los textarea ---
            const textarea = document.getElementById(field.name);
            if (textarea && textarea.value) {
                quill.root.innerHTML = textarea.value;
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formUnidad');
            const btnGuardarSemana = document.getElementById('btnGuardarSemana');
            if (form && btnGuardarSemana) {
                form.addEventListener('submit', function() {
                    btnGuardarSemana.disabled = true;
                    // Si quieres ocultarlo en vez de deshabilitarlo, usa:
                    // btnGuardarSemana.style.display = 'none';
                });
            }
        });
    </script>

    <script src="/SysPlanificacion/public/assets/js/crearUnidad.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>