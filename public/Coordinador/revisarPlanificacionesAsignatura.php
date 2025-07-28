<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'COORDINADOR') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_POST['docente_codigo']) || !isset($_POST['asignatura_codigo'])) {
    echo "<div class='alert alert-danger'>Datos insuficientes.</div>";
    exit();
}

$pdo = require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../app/RevisarPlanificaciones/getInformation.php';

$info = getDocenteYAsignatura($pdo, $_POST['docente_codigo'], $_POST['asignatura_codigo']);
$docente = $info['docente'];
$asignatura = $info['asignatura'];
$unidades = getUnidadesPorAsignatura($pdo, $_POST['asignatura_codigo']);

if (!$docente || !$asignatura) {
    echo "<div class='alert alert-danger'>No se encontraron datos.</div>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Planificación de Clase</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: #f8f9fa;
        }

        .encabezado-planificacion {
            background-color: #ffffb3;
            border: 2px solid #222;
            font-size: 0.95rem;
        }

        .encabezado-planificacion td,
        .encabezado-planificacion th {
            border: 1px solid #222 !important;
            padding: 4px 8px !important;
            vertical-align: middle;
        }

        .encabezado-planificacion th {
            background-color: #ffffb3;
            font-weight: bold;
            text-align: right;
            width: 1%;
            white-space: nowrap;
        }

        .sidebar {
            background: #343a40;
            color: #fff;
            border-radius: 0.5rem;
            padding: 1rem 0;
        }

        .sidebar .nav-link {
            color: #adb5bd;
            transition: 0.2s;
            cursor: pointer;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }

        .sidebar .list-group-item {
            background: transparent;
            color: #adb5bd;
            border: none;
            padding-left: 2.5rem;
        }

        .sidebar .list-group-item i {
            color: #ffc107;
        }

        .sidebar .collapse .list-group-item {
            padding-left: 3.5rem;
        }

        @media (max-width: 991px) {
            .encabezado-planificacion {
                font-size: 0.85rem;
            }
        }

        /* Iframe grande y responsivo */
        .iframe-container {
            width: 100%;
            min-height: 600px;
            height: 70vh;
            border: none;
            border-radius: 0.5rem;
            overflow: hidden;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <!-- Encabezado -->
        <table class="table encabezado-planificacion w-100 mx-auto mb-3 mt-3">
            <tr>
                <th>Asignatura:</th>
                <td><?= htmlspecialchars($asignatura['nombre_asignatura']); ?></td>
                <th>Código:</th>
                <td><?= htmlspecialchars($asignatura['codigo']); ?></td>
                <th>Modalidad:</th>
                <td><?= htmlspecialchars($asignatura['modalidad'] ?? 'PRESENCIAL'); ?></td>
                <th>Nivel:</th>
                <td><?= htmlspecialchars($asignatura['nivel']); ?></td>
                <th>Jornada:</th>
                <td><?= htmlspecialchars($asignatura['jornada']); ?></td>
                <th>Docente:</th>
                <td><?= htmlspecialchars($docente['nombre']); ?></td>
            </tr>
        </table>

        <!-- Contenedor principal -->
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="sidebar shadow-sm">
                    <h5 class="text-center text-white mb-4"><i class="bi bi-layers"></i> Unidades</h5>
                    <ul class="nav flex-column">
                        <?php if ($unidades): ?>
                            <?php foreach ($unidades as $unidad): ?>
                                <?php
                                $semanas = getSemanasPorUnidad($pdo, $unidad['id_unidad']);
                                $collapseId = 'unidadCollapse' . $unidad['id_unidad'];
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link px-4 unidad-link"
                                        data-bs-toggle="collapse"
                                        href="#<?= $collapseId ?>"
                                        role="button"
                                        aria-expanded="false"
                                        aria-controls="<?= $collapseId ?>"
                                        data-unidad-id="<?= $unidad['id_unidad'] ?>">
                                        <i class="bi bi-book me-2"></i> Unidad <?= htmlspecialchars($unidad['numero_unidad']); ?>: <?= htmlspecialchars($unidad['nombre']); ?>
                                    </a>
                                    
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="nav-item px-4 text-white-50">No hay unidades registradas.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Contenido principal -->
            <div class="col-md-9">
                <div class="card shadow-sm p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Planificación de la Semana</h4>
                        <!-- Botón Observaciones -->
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalObservaciones">
                            <i class="bi bi-chat-dots"></i> Observaciones
                        </button>
                    </div>
                    <iframe class="iframe-container" src="about:blank"></iframe>
                </div>
            </div>
        </div>

        <!-- Modal Observaciones -->
        <div class="modal fade" id="modalObservaciones" tabindex="-1" aria-labelledby="modalObservacionesLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="formObservaciones" method="post" action="../../app/RevisarPlanificaciones/guardarObservacion.php">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title" id="modalObservacionesLabel"><i class="bi bi-chat-dots"></i> Enviar Observación al Docente</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <!-- CORREGIDO: Ahora debe tener el codigo -->
                            <input type="hidden" name="docente_codigo" value="<?= htmlspecialchars($docente['codigo'] ?? '') ?>">
                            <input type="hidden" name="asignatura_codigo" value="<?= htmlspecialchars($asignatura['codigo'] ?? '') ?>">
                            <!-- NUEVO: Campo para capturar la unidad actual -->
                            <input type="hidden" name="unidad_id" id="unidad_id_hidden" value="">
                            
                            <!-- Mostrar información de la unidad seleccionada -->
                            <div class="alert alert-info" id="unidad_info" style="display: none;">
                                <strong>Observación para:</strong> <span id="unidad_nombre_display"></span>
                            </div>

                            <!-- Campo solo lectura para la fecha y hora de observación -->
                            <div class="mb-3 row align-items-end">
                                <div class="col-7">
                                    <label for="fecha_observacion" class="form-label">Fecha de la observación</label>
                                    <input type="text" class="form-control" id="fecha_observacion" name="fecha_observacion" value="" readonly tabindex="-1" style="background:#f8f9fa; color:#6c757d;">
                                </div>
                                <div class="col-5">
                                    <label for="hora_observacion" class="form-label">Hora</label>
                                    <input type="text" class="form-control" id="hora_observacion" name="hora_observacion" value="" readonly tabindex="-1" style="background:#f8f9fa; color:#6c757d;">
                                </div>
                            </div>

                            <!-- Alerta cuando no hay planificación -->
                            <div class="alert alert-warning" id="sin_planificacion_alert" style="display: none;">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Sin planificación:</strong> Esta unidad aún no tiene planificaciones subidas por el docente.
                            </div>

                            <div class="mb-3">
                                <label for="campo_corregir" class="form-label">¿Qué debe corregir?</label>
                                <select class="form-select" id="campo_corregir" name="campo_corregir" required>
                                    <option value="">Seleccione...</option>
                                    <option value="objetivo">Objetivo</option>
                                    <option value="apertura">Apertura</option>
                                    <option value="desarrollo">Desarrollo</option>
                                    <option value="cierre">Cierre</option>
                                    <option value="trabajo_autonomo">Trabajo autónomo</option>
                                    <option value="metodologia">Metodología</option>
                                    <option value="recursos">Recursos didácticos</option>
                                    <option value="bibliografia">Bibliografía</option>
                                    <option value="actividades_recuperacion">Actividades de recuperación</option>
                                    <option value="evaluacion">Evaluación</option>
                                    <option value="general">Observación general</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion_observacion" class="form-label">Descripción de la observación</label>
                                <textarea class="form-control" id="descripcion_observacion" name="descripcion_observacion" rows="4" required placeholder="Describa lo que debe corregir el docente"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-warning">Enviar Observación</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        let unidadActual = null; // Variable global para la unidad actual
        let tienePlanificacion = false; // Variable para saber si la unidad tiene planificación
        
        // Cargar planificación al hacer clic en unidad
        document.querySelectorAll('.unidad-link').forEach(function(link) {
            link.addEventListener('click', function(e) {
                var unidadId = this.getAttribute('data-unidad-id');
                var unidadTexto = this.textContent.trim();
                var iframe = document.querySelector('.iframe-container');
                
                if (unidadId && iframe) {
                    // Limpiar alertas previas
                    document.getElementById('unidad_info').style.display = 'none';
                    document.getElementById('sin_planificacion_alert').style.display = 'none';
                    
                    // Mostrar loading mientras se carga
                    iframe.src = "about:blank";
                    iframe.style.background = '#f8f9fa url("data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxnIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMSAxKSIgZmlsbD0iIzAwN2JmZiI+CiAgICAgICAgICAgIDxjaXJjbGUgY3g9IjUiIGN5PSI1MCIgcj0iNSI+CiAgICAgICAgICAgICAgICA8YW5pbWF0ZSBhdHRyaWJ1dGVOYW1lPSJjeS1iZWdpbiIgdmFsdWVzPSI1MDs1OzUwOzUwIiBkdXI9IjJzIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIvPgogICAgICAgICAgICA8L2NpcmNsZT4KICAgICAgICA8L2c+CiAgICA8L2c+Cjwvc3ZnPgo=") center no-repeat';
                    
                    // Intentar cargar la planificación
                    iframe.src = "../../app/RevisarPlanificaciones/getFilePlanification.php?unidad_id=" + unidadId;
                    
                    // Verificar si la planificación se carga correctamente
                    iframe.onload = function() {
                        // Verificar si hay contenido o es una página de error
                        try {
                            var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                            var hasError = iframeDoc.body.textContent.includes('Archivo no encontrado') || 
                                         iframeDoc.body.textContent.includes('No se encontró') ||
                                         iframeDoc.body.innerHTML.trim() === '';
                            
                            if (hasError) {
                                tienePlanificacion = false;
                                iframe.style.background = '#f8f9fa';
                                iframe.src = "data:text/html;charset=utf-8,<html><body style='font-family: Arial; text-align: center; padding: 50px; color: #6c757d;'><i class='bi bi-file-earmark-x' style='font-size: 48px;'></i><h4>Sin planificación</h4><p>Esta unidad aún no tiene planificaciones subidas por el docente.</p></body></html>";
                            } else {
                                tienePlanificacion = true;
                                iframe.style.background = 'white';
                            }
                        } catch (e) {
                            // Si no podemos acceder al contenido del iframe por CORS, asumimos que se cargó
                            tienePlanificacion = true;
                            iframe.style.background = 'white';
                        }
                    };
                    
                    // Error al cargar
                    iframe.onerror = function() {
                        tienePlanificacion = false;
                        iframe.style.background = '#f8f9fa';
                    };
                    
                    // Guardar la unidad actual
                    unidadActual = {
                        id: unidadId,
                        nombre: unidadTexto
                    };
                    
                    // Actualizar campos ocultos del modal
                    document.getElementById('unidad_id_hidden').value = unidadId;
                    document.getElementById('unidad_nombre_display').textContent = unidadTexto;
                    
                    // Marcar como activo
                    document.querySelectorAll('.unidad-link').forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });

        // Validar que hay una unidad seleccionada y con planificación antes de abrir el modal
        document.querySelector('[data-bs-target="#modalObservaciones"]').addEventListener('click', function(e) {
            if (!unidadActual || !unidadActual.id) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    title: '¡Seleccione una unidad!',
                    text: 'Debe seleccionar una unidad de la lista antes de enviar una observación.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#ffc107'
                });
                return false;
            }

            // Verificar si la unidad tiene planificación antes de abrir el modal
            if (tienePlanificacion === false) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    title: '¡Sin planificación!',
                    text: 'Esta unidad aún no tiene planificaciones subidas por el docente. No se pueden enviar observaciones.',
                    icon: 'info',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#0dcaf0'
                });
                return false;
            }

            // Si llegamos aquí, mostrar la información de la unidad
            document.getElementById('unidad_info').style.display = 'block';
            document.getElementById('sin_planificacion_alert').style.display = 'none';

            // Establecer la fecha y hora de observación (solo visual)
            const fechaInput = document.getElementById('fecha_observacion');
            const horaInput = document.getElementById('hora_observacion');
            const hoy = new Date();
            const fechaStr = hoy.toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' });
            const horaStr = hoy.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            fechaInput.value = fechaStr;
            horaInput.value = horaStr;
        });

        // Validación del formulario de observaciones con AJAX
        document.getElementById('formObservaciones').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var campo = document.getElementById('campo_corregir').value;
            var descripcion = document.getElementById('descripcion_observacion').value.trim();
            var unidadId = document.getElementById('unidad_id_hidden').value;
            
            if (!unidadId) {
                Swal.fire({
                    title: '¡Seleccione una unidad!',
                    text: 'Debe seleccionar una unidad antes de enviar la observación.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#ffc107'
                });
                return false;
            }
            
            if (!tienePlanificacion) {
                Swal.fire({
                    title: '¡Sin planificación!',
                    text: 'Esta unidad no tiene planificaciones. No se puede enviar la observación.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#ffc107'
                });
                return false;
            }
            
            if (!campo || !descripcion) {
                Swal.fire({
                    title: '¡Campos requeridos!',
                    text: 'Por favor complete todos los campos requeridos.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#ffc107'
                });
                return false;
            }
            
            if (descripcion.length < 10) {
                Swal.fire({
                    title: '¡Descripción muy corta!',
                    text: 'La descripción debe tener al menos 10 caracteres.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#ffc107'
                });
                return false;
            }
            
            // Confirmación con SweetAlert - mostrar la unidad específica
            Swal.fire({
                title: '¿Enviar observación?',
                text: '¿Está seguro de enviar esta observación para: ' + (unidadActual ? unidadActual.nombre : 'la unidad seleccionada') + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Enviando...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Enviar con AJAX
                    var formData = new FormData(this);
                    
                    fetch(this.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();
                        
                        if (data.success) {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'Entendido',
                                confirmButtonColor: '#198754'
                            }).then(() => {
                                // Cerrar modal y limpiar formulario
                                document.getElementById('modalObservaciones').querySelector('[data-bs-dismiss="modal"]').click();
                                this.reset();
                                document.getElementById('unidad_info').style.display = 'none';
                            });
                        } else {
                            Swal.fire({
                                title: '¡Error!',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'Entendido',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        Swal.fire({
                            title: '¡Error!',
                            text: 'Error de conexión: ' + error.message,
                            icon: 'error',
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#dc3545'
                        });
                    });
                }
            });
        });
    </script>
</body>

</html>