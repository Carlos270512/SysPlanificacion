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
                                    <div class="collapse ms-2" id="<?= $collapseId ?>">
                                        <?php if ($semanas): ?>
                                            <ul class="list-group list-group-flush">
                                                <?php foreach ($semanas as $semana): ?>
                                                    <li class="list-group-item py-1">
                                                        <i class="bi bi-calendar-week"></i>
                                                        Semana: <?= date('d/m/Y', strtotime($semana['fecha_semana'])) ?> - <?= date('d/m/Y', strtotime($semana['semana_fin'])) ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <div class="text-white-50 small ms-4">No hay semanas registradas.</div>
                                        <?php endif; ?>
                                    </div>
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
        // Cargar planificación al hacer clic en unidad
        document.querySelectorAll('.unidad-link').forEach(function(link) {
            link.addEventListener('click', function(e) {
                var unidadId = this.getAttribute('data-unidad-id');
                var iframe = document.querySelector('.iframe-container');
                if (unidadId && iframe) {
                    iframe.src = "../../app/RevisarPlanificaciones/getFilePlanification.php?unidad_id=" + unidadId;
                }
            });
        });

        // Validación del formulario de observaciones con AJAX
        document.getElementById('formObservaciones').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var campo = document.getElementById('campo_corregir').value;
            var descripcion = document.getElementById('descripcion_observacion').value.trim();
            
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
            
            // Confirmación con SweetAlert
            Swal.fire({
                title: '¿Enviar observación?',
                text: '¿Está seguro de enviar esta observación al docente?',
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