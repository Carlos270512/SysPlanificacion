<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    header("Location: ../../index.php");
    exit();
}

// Incluir conexión y repository
$pdo = require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../app/repositoryPlanificaciones/RepositoryPlanificacionesRepository.php';

// Instanciar repository
$repository = new RepositoryPlanificacionesRepository($pdo);

// Obtener planificaciones (con filtros si se aplican)
$asignaturaFiltro = $_GET['asignatura'] ?? null;
$periodoFiltro = $_GET['periodo'] ?? null;

if ($asignaturaFiltro || $periodoFiltro) {
    $planificaciones = $repository->obtenerPlanificacionesFiltradas($asignaturaFiltro, $periodoFiltro);
} else {
    $planificaciones = $repository->obtenerTodasLasPlanificaciones();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Repositorio de Planificaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/planificaciontyle.css">
</head>

<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-archive me-2"></i>Repositorio de Planificaciones</h4>
                    <button type="button" class="btn btn-descargar-cafe" id="btnDescargarSeleccionados"
                        onclick="descargarSeleccionados()" disabled>
                        <i class="fas fa-download me-2"></i>Descargar Seleccionados
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filtros -->
                <form method="get" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="asignatura" class="form-label fw-semibold">Filtrar por Asignatura</label>
                        <input type="text" class="form-control" id="asignatura" name="asignatura"
                            placeholder="Ingrese nombre de asignatura..."
                            value="<?php echo htmlspecialchars($asignaturaFiltro ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="periodo" class="form-label fw-semibold">Filtrar por Período</label>
                        <input type="text" class="form-control" id="periodo" name="periodo"
                            placeholder="Ingrese período lectivo..."
                            value="<?php echo htmlspecialchars($periodoFiltro ?? ''); ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-filtrar-custom me-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>

                <!-- Tabla de Planificaciones -->
                <div class="table-responsive">
                    <table id="tablaPlanificaciones" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Archivo</th>
                                <th>Tipo MIME</th>
                                <th>Asignatura</th>
                                <th>Período Lectivo</th>
                                <th>Fecha Creación</th>
                                <th>Usuario Creación</th>
                                <th>Acciones</th>
                                <th>
                                    Seleccionar
                                    <input type="checkbox" id="selectAll" class="form-check-input ms-2"
                                        title="Seleccionar todos" onchange="toggleSelectAll()">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($planificaciones as $planificacion): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($planificacion['id_repository']); ?></td>
                                    <td><?php echo htmlspecialchars($planificacion['nombre_archivo']); ?></td>
                                    <td><?php echo htmlspecialchars($planificacion['tipo_mime']); ?></td>
                                    <td><?php echo htmlspecialchars($planificacion['asignatura'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($planificacion['periodo_lectivo']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($planificacion['fecha_creacion'])); ?></td>
                                    <td><?php echo htmlspecialchars($planificacion['usuario_creacion'] ?? 'N/A'); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-ver-cafe btn-sm"
                                            onclick="verPlanificacion(<?php echo $planificacion['id_repository']; ?>)"
                                            title="Ver Planificación">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <input type="checkbox" class="form-check-input planificacion-checkbox"
                                            value="<?php echo $planificacion['id_repository']; ?>"
                                            onchange="actualizarBotonDescarga()">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#tablaPlanificaciones').DataTable({
                "language": {
                    "decimal": "",
                    "emptyTable": "No hay datos disponibles en la tabla",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron registros coincidentes",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": activar para ordenar la columna ascendente",
                        "sortDescending": ": activar para ordenar la columna descendente"
                    }
                },
                "pageLength": 25,
                "responsive": true,
                "order": [
                    [0, "desc"]
                ],
                "columnDefs": [{
                    "targets": [7, 8], // Columnas de acciones y checkbox
                    "orderable": false
                }]
            });
        });

        // Función para seleccionar/deseleccionar todos los checkboxes
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.planificacion-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });

            actualizarBotonDescarga();
        }

        // Función para actualizar el estado del botón de descarga
        function actualizarBotonDescarga() {
            const selected = obtenerSeleccionados();
            const btnDescargar = document.getElementById('btnDescargarSeleccionados');

            if (selected.length > 0) {
                btnDescargar.disabled = false;
                btnDescargar.innerHTML = `<i class="fas fa-download me-2"></i>Descargar Seleccionados (${selected.length})`;
            } else {
                btnDescargar.disabled = true;
                btnDescargar.innerHTML = '<i class="fas fa-download me-2"></i>Descargar Seleccionados';
            }
        }

        function verPlanificacion(idRepository) {
            const pdfFrame = document.getElementById('pdfFrame');
            pdfFrame.src = `../../app/repositoryPlanificaciones/verPlanificacion.php?id=${idRepository}`;

            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();
        }

        // Función para obtener planificaciones seleccionadas
        function obtenerSeleccionados() {
            const selected = [];
            const checkboxes = document.querySelectorAll('.planificacion-checkbox:checked');
            checkboxes.forEach(checkbox => {
                selected.push(checkbox.value);
            });
            return selected;
        }

        // Función para descargar planificaciones seleccionadas
        function descargarSeleccionados() {
            const selected = obtenerSeleccionados();

            if (selected.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin selección',
                    text: 'Por favor seleccione al menos una planificación para descargar.',
                });
                return;
            }

            Swal.fire({
                title: '¿Descargar planificaciones?',
                text: `Se descargarán ${selected.length} planificación(es) en un archivo ZIP.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, descargar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Generando archivo...',
                        text: 'Por favor espere mientras se prepara la descarga.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Crear formulario para enviar IDs
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '../../app/repositoryPlanificaciones/descargarSeleccionados.php';
                    form.target = '_blank';

                    selected.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });

                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);

                    // Cerrar loading después de un momento
                    setTimeout(() => {
                        Swal.close();
                    }, 2000);
                }
            });
        }
    </script>
</body>

</html>