<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'COORDINADOR') {
    header("Location: ../index.php");
    exit();
}

// Conexión a la base de datos
$pdo = require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../app/CoevaluacionBack/coevaluacion_Repository.php';

// Inicializar repository
$repo = new CoevaluacionRepository($pdo);

// Obtener datos del coordinador logueado
$coordinadorLogueado = $repo->getDocentePorCodigo($_SESSION['usuario']['codigo']);

if (!$coordinadorLogueado) {
    echo "<script>alert('Error: No se encontraron datos del coordinador'); window.location='../index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generación de Planificación de Coevaluaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/coevaluacionStyle.css?v=2">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="form-container">
                    <!-- Título principal -->
                    <div class="section-title text-center">
                        <h2><i class="fas fa-clipboard-list me-2"></i>Generación de Planificación de Coevaluaciones</h2>
                    </div>

                    <form id="formCoevaluacion" method="POST" action="">
                        <!-- Fecha del documento -->
                        <div class="form-group-custom">
                            <h5 class="mb-3"><i class="fas fa-calendar me-2 text-cafe-oscuro"></i>Fecha del Documento</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="fecha_documento" class="form-label fw-bold">Fecha del documento:</label>
                                    <input type="date" class="form-control" id="fecha_documento" name="fecha_documento"
                                        value="<?php echo date('Y-m-d'); ?>" required>
                                    <small class="text-muted">Esta fecha aparecerá como: "Quito, 17 de junio de 2025"</small>
                                </div>
                            </div>
                        </div>

                        <!-- Datos del coordinador (automáticos) -->
                        <div class="form-group-custom">
                            <h5 class="mb-3"><i class="fas fa-user me-2 text-cafe-oscuro"></i>Datos del Coordinador (DE:)</h5>
                            <div class="alert alert-info">
                                <strong><?php echo $coordinadorLogueado['titulo_abreviado']; ?></strong><br>
                                <?php echo htmlspecialchars($coordinadorLogueado['nombre']); ?><br>
                                Coordinador de Carrera de <?php echo htmlspecialchars($coordinadorLogueado['carrera']); ?>
                            </div>
                        </div>

                        <!-- Modalidad -->
                        <div class="form-group-custom">
                            <h5 class="mb-3"><i class="fas fa-graduation-cap me-2 text-cafe-oscuro"></i>Modalidad de Estudio</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="modalidad" class="form-label fw-bold">Seleccione la modalidad:</label>
                                    <select class="form-select" id="modalidad" name="modalidad" required>
                                        <option value="">-- Seleccione modalidad --</option>
                                        <option value="presencial">Presencial</option>
                                        <option value="en_linea">En línea</option>
                                        <option value="hibrida">Híbrida</option>
                                        <option value="semi_presencial">Semi-presencial</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Período de coevaluaciones -->
                        <div class="form-group-custom">
                            <h5 class="mb-3"><i class="fas fa-calendar-alt me-2 text-cafe-oscuro"></i>Período de Coevaluaciones</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="fecha_inicio" class="form-label fw-bold">Fecha de inicio:</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha_fin" class="form-label fw-bold">Fecha de fin:</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                </div>
                            </div>
                            <small class="text-muted">
                                Estas fechas se mostrarán en el texto: "...las coevaluaciones que se realizarán desde el [fecha inicio] al [fecha fin]."
                            </small>
                        </div>

                        <!-- NUEVA SECCIÓN: Docentes para coevaluar -->
                        <div class="form-group-custom">
                            <h5 class="mb-3"><i class="fas fa-users me-2 text-cafe-oscuro"></i>Docentes para Coevaluar</h5>

                            <!-- Selector de docente -->
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="docente_coevaluar" class="form-label fw-bold">Seleccione docente:</label>
                                    <select class="form-select" id="docente_coevaluar" name="docente_coevaluar" onchange="cargarAsignaturas()">
                                        <option value="">-- Seleccione un docente para coevaluar --</option>
                                        <?php
                                        $docentes = $repo->getDocentesParaCoevaluar($_SESSION['usuario']['codigo']);
                                        foreach ($docentes as $docente) {
                                            echo "<option value='{$docente['codigo']}'>{$docente['codigo']} - {$docente['nombre']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Selector de asignatura (se activa cuando se selecciona docente) -->
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="asignatura_coevaluar" class="form-label fw-bold">Seleccione asignatura:</label>
                                    <select class="form-select" id="asignatura_coevaluar" name="asignatura_coevaluar" disabled onchange="activarFechaHora()">
                                        <option value="">-- Primero seleccione un docente --</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Fecha y hora de coevaluación (se activa cuando se selecciona asignatura) -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="fecha_coevaluacion" class="form-label fw-bold">Fecha de coevaluación:</label>
                                    <input type="date" class="form-control" id="fecha_coevaluacion" name="fecha_coevaluacion" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label for="hora_coevaluacion" class="form-label fw-bold">Hora:</label>
                                    <select class="form-select" id="hora_coevaluacion" name="hora_coevaluacion" disabled>
                                        <option value="">-- Seleccione hora --</option>
                                        <option value="08:00-10:00">08:00 - 10:00</option>
                                        <option value="10:00-12:00">10:00 - 12:00</option>
                                        <option value="18:00-20:00">18:00 - 20:00</option>
                                        <option value="20:00-22:00">20:00 - 22:00</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tipo_observacion" class="form-label fw-bold">Tipo de observación:</label>
                                    <select class="form-select" id="tipo_observacion" name="tipo_observacion" onchange="manejarObservacion()">
                                        <option value="">-- Seleccione tipo --</option>
                                        <option value="Con carga horaria">Con carga horaria</option>
                                        <option value="Sin carga horaria">Sin carga horaria</option>
                                        <option value="personalizada">Escribir observación personalizada</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="observaciones_texto" class="form-label fw-bold">Observaciones:</label>
                                    <input type="text" class="form-control" id="observaciones_texto" name="observaciones_texto"
                                        placeholder="Las observaciones aparecerán automáticamente" readonly>
                                </div>
                            </div>

                            <!-- Botón para agregar a la tabla -->
                            <div class="text-center">
                                <button type="button" class="btn btn-cafe" onclick="agregarCoevaluacion()" disabled id="btnAgregar">
                                    <i class="fas fa-plus me-2"></i>Agregar Coevaluación
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de coevaluaciones agregadas -->
                        <div class="form-group-custom">
                            <h5 class="mb-3"><i class="fas fa-table me-2 text-cafe-oscuro"></i>Coevaluaciones Programadas</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="tablaCoevaluaciones">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>CARRERA</th>
                                            <th>MODALIDAD</th>
                                            <th>DOCENTE (CÓDIGO)</th>
                                            <th>FECHA</th>
                                            <th>HORA</th>
                                            <th>ASIGNATURA (CÓDIGO)</th>
                                            <th>OBSERVACIONES</th>
                                            <th>ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Las filas se agregarán dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Botón para generar -->
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn btn-cafe btn-lg" onclick="generarPDF()">
                                <i class="fas fa-file-pdf me-2"></i>Ver PDF de Coevaluación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar PDF -->
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">
                        <i class="fas fa-file-pdf me-2"></i>Documento de Coevaluación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="pdfViewer" width="100%" height="600px" style="border: none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-cafe me-2" onclick="guardarPDF()">
                        <i class="fas fa-save me-2"></i>Guardar PDF
                    </button>
                    <button type="button" class="btn btn-primary" onclick="descargarPDF()">
                        <i class="fas fa-download me-2"></i>Descargar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Array para almacenar las coevaluaciones
        let coevaluaciones = [];

        // Validar fechas del período
        document.getElementById('fecha_fin').addEventListener('change', function() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = this.value;

            if (fechaInicio && fechaFin && fechaFin <= fechaInicio) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fecha incorrecta',
                    text: 'La fecha de fin debe ser posterior a la fecha de inicio',
                    confirmButtonColor: '#3085d6'
                });
                this.value = '';
            }
        });

        // Cargar asignaturas cuando se selecciona un docente - RUTA CORREGIDA
        function cargarAsignaturas() {
            const docenteCodigo = document.getElementById('docente_coevaluar').value;
            const selectAsignatura = document.getElementById('asignatura_coevaluar');

            // Reset campos
            selectAsignatura.innerHTML = '<option value="">-- Cargando asignaturas... --</option>';
            selectAsignatura.disabled = true;
            desactivarCampos();

            if (docenteCodigo) {
                // RUTA CORREGIDA para el AJAX
                fetch('../../app/CoevaluacionBack/getAsignaturas.php?docente_codigo=' + docenteCodigo)
                    .then(response => response.json())
                    .then(data => {
                        selectAsignatura.innerHTML = '<option value="">-- Seleccione una asignatura --</option>';
                        data.forEach(asignatura => {
                            selectAsignatura.innerHTML += `<option value="${asignatura.codigo}">${asignatura.codigo} - ${asignatura.nombre_asignatura}</option>`;
                        });
                        selectAsignatura.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        selectAsignatura.innerHTML = '<option value="">-- Error al cargar asignaturas --</option>';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al cargar las asignaturas del docente',
                            confirmButtonColor: '#3085d6'
                        });
                    });
            } else {
                selectAsignatura.innerHTML = '<option value="">-- Primero seleccione un docente --</option>';
            }
        }

        // Activar campos de fecha y hora cuando se selecciona asignatura
        function activarFechaHora() {
            const asignatura = document.getElementById('asignatura_coevaluar').value;
            const fechaCoevaluacion = document.getElementById('fecha_coevaluacion');
            const horaCoevaluacion = document.getElementById('hora_coevaluacion');

            if (asignatura) {
                fechaCoevaluacion.disabled = false;
                horaCoevaluacion.disabled = false;
                validarBotonAgregar();
            } else {
                fechaCoevaluacion.disabled = true;
                horaCoevaluacion.disabled = true;
                desactivarBotonAgregar();
            }
        }

        // Manejar las observaciones - PERMITIR EDITAR MANUALMENTE
        function manejarObservacion() {
            const tipoObservacion = document.getElementById('tipo_observacion').value;
            const observacionesTexto = document.getElementById('observaciones_texto');

            if (tipoObservacion === 'Con carga horaria') {
                observacionesTexto.value = 'Con carga horaria';
                observacionesTexto.readOnly = false; // PERMITIR EDITAR
            } else if (tipoObservacion === 'Sin carga horaria') {
                observacionesTexto.value = 'Sin carga horaria';
                observacionesTexto.readOnly = false; // PERMITIR EDITAR
            } else if (tipoObservacion === 'personalizada') {
                observacionesTexto.value = '';
                observacionesTexto.readOnly = false;
                observacionesTexto.placeholder = 'Escriba su observación personalizada...';
            } else {
                observacionesTexto.value = '';
                observacionesTexto.readOnly = true;
                observacionesTexto.placeholder = 'Las observaciones aparecerán automáticamente';
            }

            validarBotonAgregar();
        }

        // Validar si se puede activar el botón agregar
        function validarBotonAgregar() {
            const docente = document.getElementById('docente_coevaluar').value;
            const asignatura = document.getElementById('asignatura_coevaluar').value;
            const fecha = document.getElementById('fecha_coevaluacion').value;
            const hora = document.getElementById('hora_coevaluacion').value;
            const tipoObservacion = document.getElementById('tipo_observacion').value;
            const observaciones = document.getElementById('observaciones_texto').value;

            const btnAgregar = document.getElementById('btnAgregar');

            if (docente && asignatura && fecha && hora && tipoObservacion && observaciones.trim()) {
                btnAgregar.disabled = false;
            } else {
                btnAgregar.disabled = true;
            }
        }

        // Desactivar campos cuando no hay selección
        function desactivarCampos() {
            document.getElementById('fecha_coevaluacion').disabled = true;
            document.getElementById('hora_coevaluacion').disabled = true;
            document.getElementById('fecha_coevaluacion').value = '';
            document.getElementById('hora_coevaluacion').value = '';
            document.getElementById('tipo_observacion').value = '';
            document.getElementById('observaciones_texto').value = '';
            desactivarBotonAgregar();
        }

        function desactivarBotonAgregar() {
            document.getElementById('btnAgregar').disabled = true;
        }

        // Agregar coevaluación a la tabla
        function agregarCoevaluacion() {
            const modalidad = document.getElementById('modalidad').value;
            const docenteSelect = document.getElementById('docente_coevaluar');
            const asignaturaSelect = document.getElementById('asignatura_coevaluar');
            const fecha = document.getElementById('fecha_coevaluacion').value;
            const hora = document.getElementById('hora_coevaluacion').value;
            const observaciones = document.getElementById('observaciones_texto').value;

            if (!modalidad) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Modalidad requerida',
                    text: 'Por favor seleccione la modalidad antes de agregar coevaluaciones',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            const coevaluacion = {
                carrera: '<?php echo $coordinadorLogueado['carrera']; ?>',
                modalidad: modalidad.charAt(0).toUpperCase() + modalidad.slice(1).replace('_', ' '),
                docente_codigo: docenteSelect.value,
                docente_nombre: docenteSelect.options[docenteSelect.selectedIndex].text,
                fecha: fecha,
                hora: hora,
                asignatura_codigo: asignaturaSelect.value,
                asignatura_nombre: asignaturaSelect.options[asignaturaSelect.selectedIndex].text,
                observaciones: observaciones
            };

            // Agregar al array
            coevaluaciones.push(coevaluacion);

            // Actualizar tabla
            actualizarTabla();

            // Limpiar formulario
            limpiarFormularioCoevaluacion();

            // Mensaje de éxito
            Swal.fire({
                icon: 'success',
                title: '¡Coevaluación agregada!',
                text: 'La coevaluación se ha agregado correctamente a la tabla',
                timer: 1500,
                showConfirmButton: false
            });
        }

        // Actualizar la tabla de coevaluaciones
        function actualizarTabla() {
            const tbody = document.querySelector('#tablaCoevaluaciones tbody');
            tbody.innerHTML = '';

            coevaluaciones.forEach((coevaluacion, index) => {
                const fechaFormateada = new Date(coevaluacion.fecha + 'T00:00:00').toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });

                const fila = `
                    <tr>
                        <td>${coevaluacion.carrera}</td>
                        <td>${coevaluacion.modalidad}</td>
                        <td>${coevaluacion.docente_nombre}</td>
                        <td>${fechaFormateada}</td>
                        <td>${coevaluacion.hora}</td>
                        <td>${coevaluacion.asignatura_nombre}</td>
                        <td>${coevaluacion.observaciones}</td>
                        <td>
                            <button type="button" class="btn btn-delete btn-sm" onclick="eliminarCoevaluacion(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += fila;
            });
        }

        // Eliminar coevaluación
        function eliminarCoevaluacion(index) {
            Swal.fire({
                title: '¿Eliminar coevaluación?',
                text: '¿Está seguro de que desea eliminar esta coevaluación?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    coevaluaciones.splice(index, 1);
                    actualizarTabla();
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: 'La coevaluación ha sido eliminada',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }

        // Limpiar formulario de coevaluación
        function limpiarFormularioCoevaluacion() {
            document.getElementById('docente_coevaluar').value = '';
            document.getElementById('asignatura_coevaluar').innerHTML = '<option value="">-- Primero seleccione un docente --</option>';
            document.getElementById('asignatura_coevaluar').disabled = true;
            document.getElementById('fecha_coevaluacion').value = '';
            document.getElementById('hora_coevaluacion').value = '';
            document.getElementById('tipo_observacion').value = '';
            document.getElementById('observaciones_texto').value = '';
            desactivarCampos();
        }

        // Agregar listeners para validación en tiempo real
        document.getElementById('fecha_coevaluacion').addEventListener('change', validarBotonAgregar);
        document.getElementById('hora_coevaluacion').addEventListener('change', validarBotonAgregar);
        document.getElementById('observaciones_texto').addEventListener('input', validarBotonAgregar);

        function generarPDF() {
            // Validar formulario básico
            const form = document.getElementById('formCoevaluacion');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            if (coevaluaciones.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin coevaluaciones',
                    text: 'Debe agregar al menos una coevaluación para generar el PDF',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Obtener datos del formulario
            const formData = new FormData(form);
            formData.append('generar_pdf', '1');
            formData.append('coevaluaciones', JSON.stringify(coevaluaciones));

            // Crear URL para el PDF - RUTA CORREGIDA
            const params = new URLSearchParams(formData);
            const pdfUrl = '../../app/CoevaluacionBack/coevaluacionPDF.php?' + params.toString();

            // Mostrar en modal
            document.getElementById('pdfViewer').src = pdfUrl;
            new bootstrap.Modal(document.getElementById('pdfModal')).show();
        }

        function guardarPDF() {
            if (coevaluaciones.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin coevaluaciones',
                    text: 'No hay coevaluaciones para guardar',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Validar formulario básico
            const form = document.getElementById('formCoevaluacion');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Obtener datos para mostrar en la confirmación
            const fechaDocumento = document.getElementById('fecha_documento').value;
            const modalidad = document.getElementById('modalidad').options[document.getElementById('modalidad').selectedIndex].text;
            const coordi = '<?php echo $coordinadorLogueado['carrera']; ?>';
            const fechaFormateada = new Date(fechaDocumento).toLocaleDateString('es-ES');
            const nombreArchivo = `coevaluacion_${coordi}_${fechaDocumento}.pdf`;

            // Confirmación con información del archivo
            Swal.fire({
                title: 'Confirmar guardado',
                html: `
                    <div style="text-align: left;">
                        <p><strong>¿Desea guardar el PDF en el repositorio?</strong></p>
                        <hr>
                        <p><i class="fas fa-file-pdf text-danger"></i> <strong>Archivo:</strong> ${nombreArchivo}</p>
                        <p><i class="fas fa-user text-primary"></i> <strong>Carrera:</strong> ${coordi}</p>
                        <p><i class="fas fa-graduation-cap text-success"></i> <strong>Modalidad:</strong> ${modalidad}</p>
                        <p><i class="fas fa-calendar text-info"></i> <strong>Fecha:</strong> ${fechaFormateada}</p>
                        <p><i class="fas fa-list text-warning"></i> <strong>Coevaluaciones:</strong> ${coevaluaciones.length}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-save"></i> Sí, guardar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                focusConfirm: false
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarGuardado();
                }
            });
        }

        function ejecutarGuardado() {
            // Preparar datos para enviar
            const form = document.getElementById('formCoevaluacion');
            const formData = new FormData(form);
            formData.append('coevaluaciones', JSON.stringify(coevaluaciones));
            formData.append('guardar_pdf', '1');

            // Mostrar loading
            Swal.fire({
                title: 'Guardando PDF...',
                html: '<i class="fas fa-spinner fa-spin fa-2x"></i><br><br>Por favor espere mientras se guarda el documento',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // CAMBIAR LA RUTA AQUÍ
            fetch('../../app/CoevaluacionBack/guardar_coevaluacion.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡PDF guardado exitosamente!',
                            html: `
                    <div style="text-align: left;">
                        <p><i class="fas fa-check-circle text-success"></i> El documento ha sido guardado en el repositorio</p>
                        <hr>
                        <p><i class="fas fa-file-pdf text-danger"></i> <strong>Archivo:</strong> ${data.archivo}</p>
                        <p><i class="fas fa-database text-info"></i> <strong>ID:</strong> ${data.id}</p>
                    </div>
                `,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: '<i class="fas fa-thumbs-up"></i> ¡Perfecto!'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al guardar',
                            text: data.error || 'Error desconocido al guardar el PDF',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor para guardar el PDF',
                        confirmButtonColor: '#dc3545'
                    });
                });
        }

        function descargarPDF() {
            const pdfUrl = document.getElementById('pdfViewer').src;
            const link = document.createElement('a');
            link.href = pdfUrl + '&download=1';
            link.download = 'coevaluacion_' + new Date().toISOString().split('T')[0] + '.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>

</html>