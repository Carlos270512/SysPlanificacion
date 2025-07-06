<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    header("Location: ../index.php");
    exit();
}

// Incluir conexión y repository
$pdo = require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../app/GestionPlanificaciones.php/GestionPlanificacionesRepository.php';

// Instanciar repository
$repository = new GestionPlanificacionesRepository($pdo);

// Obtener todos los docentes activos
$docentes = $repository->obtenerDocentesActivos();

// Si se seleccionó un docente, obtener sus asignaturas
$asignaturas = [];
$docenteSeleccionado = null;
if (isset($_GET['docente_codigo']) && $_GET['docente_codigo'] !== '') {
    $codigoDocente = $_GET['docente_codigo'];
    $asignaturas = $repository->obtenerAsignaturasPorDocente($codigoDocente);
    $docenteSeleccionado = $repository->obtenerDocentePorCodigo($codigoDocente);
}

// Si se seleccionó una asignatura, obtener sus datos y planificaciones
$asignaturaSeleccionada = null;
$planificaciones = [];
if (isset($_GET['asignatura_codigo']) && $_GET['asignatura_codigo'] !== '') {
    $asignaturaSeleccionada = $repository->obtenerAsignaturaPorCodigo($_GET['asignatura_codigo']);
    $planificaciones = $repository->obtenerPlanificacionesActivasPorAsignatura($_GET['asignatura_codigo']);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Planificaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-search me-2"></i>Gestión de Planificaciones</h4>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="docente_codigo" class="form-label fw-semibold">Seleccione Docente</label>
                        <select class="form-select" id="docente_codigo" name="docente_codigo" required onchange="this.form.submit()">
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($docentes as $doc): ?>
                                <option value="<?php echo htmlspecialchars($doc['codigo']); ?>"
                                    <?php if (isset($_GET['docente_codigo']) && $_GET['docente_codigo'] == $doc['codigo']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($doc['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($asignaturas): ?>
                        <div class="col-md-6">
                            <label for="asignatura_codigo" class="form-label fw-semibold">Asignaturas del Docente</label>
                            <select class="form-select" id="asignatura_codigo" name="asignatura_codigo" onchange="this.form.submit()">
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($asignaturas as $asig): ?>
                                    <option value="<?php echo htmlspecialchars($asig['codigo']); ?>"
                                        <?php if (isset($_GET['asignatura_codigo']) && $_GET['asignatura_codigo'] == $asig['codigo']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($asig['nombre_asignatura']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </form>

                <?php if ($asignaturaSeleccionada): ?>
                    <!-- Datos de la Asignatura -->
                    <div class="card border-primary mt-4">
                        <div class="card-header bg-primary text-white">
                            <strong>Datos de la Asignatura</strong>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Nombre:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['nombre_asignatura']); ?></div>
                                <div class="col-md-6"><strong>Código:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['codigo']); ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Horario:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['horario']); ?></div>
                                <div class="col-md-4"><strong>Jornada:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['jornada']); ?></div>
                                <div class="col-md-4"><strong>Aula:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['aula']); ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Nivel:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['nivel']); ?></div>
                                <div class="col-md-4"><strong>Fecha Inicio:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['fecha_inicio']); ?></div>
                                <div class="col-md-4"><strong>Fecha Fin:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['fecha_fin']); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Planificaciones -->
                    <div class="card border-success mt-4">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <strong>Planificaciones de la Asignatura</strong>
                            <div>
                                <button type="button" class="btn btn-warning btn-sm me-2" onclick="descargarSeleccionados()">
                                    <i class="fas fa-download"></i> Descargar Seleccionados
                                </button>
                            </div>
                            <button type="button" class="btn btn-light btn-sm" onclick="toggleSelectAll()">
                                <i class="fas fa-check-square"></i> Seleccionar Todo
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (count($planificaciones) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID Unidad</th>
                                                <th>Nombre Archivo</th>
                                                <th>Fecha Creación</th>
                                                <th>Última Actualización</th>
                                                <th>Usuario Creación</th>
                                                <th>Acciones</th>
                                                <th>
                                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($planificaciones as $planificacion): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($planificacion['numero_unidad']); ?></td>
                                                    <td><?php echo htmlspecialchars($planificacion['nombre_archivo']); ?></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($planificacion['fecha_creacion'])); ?></td>
                                                    <td><?php echo $planificacion['fecha_actualizacion'] ? date('d/m/Y H:i', strtotime($planificacion['fecha_actualizacion'])) : 'N/A'; ?></td>
                                                    <td><?php echo htmlspecialchars($planificacion['usuario_creacion'] ?? 'N/A'); ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-info btn-sm me-1"
                                                            onclick="verArchivo(<?php echo $planificacion['id_planificacion']; ?>)"
                                                            title="Ver Archivo">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="eliminarPlanificacion(<?php echo $planificacion['id_planificacion']; ?>)"
                                                            title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="planificacion-checkbox"
                                                            value="<?php echo $planificacion['id_planificacion']; ?>">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No hay planificaciones disponibles para esta asignatura.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($docenteSeleccionado): ?>
                    <div class="alert alert-info mt-4">Seleccione una asignatura para ver las planificaciones.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para ver PDF -->
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Visualizar Planificación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="pdfFrame" src="" style="width:100%; height:600px;" frameborder="0"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

        //este igual mover a assts en una carpeta de gestion
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.planificacion-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }

        function verArchivo(idPlanificacion) {
            const pdfFrame = document.getElementById('pdfFrame');
            pdfFrame.src = `../app/GestionPlanificaciones.php/getPlanificacionPorAsignatura.php?id=${idPlanificacion}`;

            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();
        }

        function eliminarPlanificacion(idPlanificacion) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Realizar la eliminación
                    fetch('../app/GestionPlanificaciones.php/deletePlanificacionesPorAsignatura.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                id: idPlanificacion
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    '¡Eliminado!',
                                    data.message,
                                    'success'
                                ).then(() => {
                                    // Recargar la página para actualizar la tabla
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error',
                                    data.message,
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error',
                                'Error de conexión: ' + error.message,
                                'error'
                            );
                        });
                }
            });
        }

        function getSelectedPlanifications() {
            const selected = [];
            const checkboxes = document.querySelectorAll('.planificacion-checkbox:checked');
            checkboxes.forEach(checkbox => {
                selected.push(checkbox.value);
            });
            return selected;
        }
    </script>

    <script>
        //     // ...este escript mover a una carpeta assets de igual forma en una caperta de gesstion separado todo


        function descargarSeleccionados() {
            const selected = getSelectedPlanifications();

            if (selected.length === 0) {
                Swal.fire({
                    title: 'Atención',
                    text: 'Debe seleccionar al menos una planificación para descargar',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            Swal.fire({
                title: 'Descargando...',
                text: `Preparando descarga de ${selected.length} planificacion(es)`,
                icon: 'info',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();

                    // Realizar la descarga
                    fetch('../app/GestionPlanificaciones.php/downloadPlanificacionesMasivas.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                ids: selected
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error en la descarga');
                            }
                            return response.blob();
                        })
                        .then(blob => {
                            // Crear enlace de descarga
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = `planificaciones_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.zip`;
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);

                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'Descarga completada exitosamente',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error',
                                text: 'Error al descargar los archivos: ' + error.message,
                                icon: 'error'
                            });
                        });
                }
            });
        }

        
    </script>
</body>

</html>