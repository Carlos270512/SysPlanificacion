<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    header("Location: ../index.php");
    exit();
}
require __DIR__ . '/../../config/conexion.php';
$stmtDocentes = $pdo->query("SELECT codigo, nombre FROM docente WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
$docentes = $stmtDocentes->fetchAll(PDO::FETCH_ASSOC);

$mensaje = '';
if (isset($_GET['exito'])) {
    $mensaje = '<div class="alert alert-success">Archivo Excel cargado correctamente.</div>';
} elseif (isset($_GET['error'])) {
    $mensaje = '<div class="alert alert-danger">Hubo un error al procesar el archivo.</div>';
} elseif (isset($_GET['error_encabezados'])) {
    $mensaje = '<div class="alert alert-danger">Los encabezados del archivo no son válidos. Por favor, verifica el formato.</div>';
} elseif (isset($_GET['error_subida'])) {
    $mensaje = '<div class="alert alert-danger">Error al subir el archivo. Asegúrate de que sea un archivo Excel válido.</div>';
} elseif (isset($_GET['error_formato'])) {
    $mensaje = '<div class="alert alert-danger">El archivo subido no es un archivo Excel válido. Por favor, verifica el formato.</div>';
} elseif (isset($_GET['archivo_duplicado'])) {
    //$mensaje = '<div class="alert alert-warning">Este archivo ya fue subido anteriormente.</div>';
}

$hayErrores = isset($_GET['errores']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Subir Excel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/subirExcelstyles.css">
    <script src="../assets/js/Administrador/toast.js"></script>
</head>

<body>
    <h2 class="mb-4">Subir Asignaturas</h2>

    <!-- Botones de acción -->
    <div class="mb-4">
        <form action="../../app/Operaciones/procesarExcel.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="archivo_excel" class="form-label">Selecciona el archivo Excel:</label>
                <input class="form-control" type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls" required>
            </div>
            <button class="btn btn-cafe mb-2" type="submit" name="submit">
                <i class="fas fa-upload me-1"></i>Subir Excel
            </button>
            <button class="btn btn-success mb-2 ms-2" data-bs-toggle="modal" data-bs-target="#nuevaAsignaturaModal" type="button">
                <i class="fas fa-plus me-1"></i>Nueva Asignatura
            </button>
        </form>
    </div>

    <!-- Tabla para mostrar los datos subidos -->
    <div class="table-responsive">
        <table id="asignaturasTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Asignatura</th>
                    <th>Horario</th>
                    <th>Jornada</th>
                    <th>Periodo Lectivo</th>
                    <th>Aula</th>
                    <th>Nivel</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Codigo Profesor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require __DIR__ . '/../../config/conexion.php';
                $stmt = $pdo->query("SELECT codigo, nombre_asignatura, horario, jornada, periodo_academico, aula, nivel, fecha_inicio, fecha_fin, docente_codigo FROM asignatura");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['codigo']) ?></td>
                        <td><?= htmlspecialchars($row['nombre_asignatura']) ?></td>
                        <td><?= htmlspecialchars($row['horario']) ?></td>
                        <td><?= htmlspecialchars($row['jornada']) ?></td>
                        <td><?= htmlspecialchars($row['periodo_academico']) ?></td>
                        <td><?= htmlspecialchars($row['aula']) ?></td>
                        <td><?= htmlspecialchars($row['nivel']) ?></td>
                        <td><?= htmlspecialchars($row['fecha_inicio']) ?></td>
                        <td><?= htmlspecialchars($row['fecha_fin']) ?></td>
                        <td><?= htmlspecialchars($row['docente_codigo'] ?? '') ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                    onclick="editarAsignatura('<?= $row['codigo'] ?>')"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                    onclick="eliminarAsignatura('<?= $row['codigo'] ?>', '<?= htmlspecialchars($row['nombre_asignatura']) ?>')"
                                    title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Nueva Asignatura -->
    <div class="modal fade" id="nuevaAsignaturaModal" tabindex="-1" aria-labelledby="nuevaAsignaturaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="nuevaAsignaturaModalLabel">
                        <i class="fas fa-plus me-2"></i>Nueva Asignatura
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNuevaAsignatura">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" placeholder="Ej: 2425101" required>
                                    <small id="error_codigo" class="form-text text-danger d-none">Solo se aceptan números</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre_asignatura" class="form-label">Asignatura <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre_asignatura" name="nombre_asignatura" placeholder="Ej: MATEMÁTICAS" required>
                                    <small id="error_nombre_asignatura" class="form-text text-danger d-none">Solo se aceptan letras y espacios</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="horario" class="form-label">Horario</label>
                                    <input type="text" class="form-control" id="horario" name="horario" placeholder="Ej: 08-10">
                                    <small id="error_horario" class="form-text text-danger d-none">Formato válido: 08-10</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jornada" class="form-label">Jornada</label>
                                    <select class="form-control" id="jornada" name="jornada">
                                        <option value="">Seleccionar...</option>
                                        <option value="MATUTINA">MATUTINA</option>
                                        <option value="VESPERTINA">VESPERTINA</option>
                                        <option value="NOCTURNA">NOCTURNA</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="periodo_academico" class="form-label">Periodo Lectivo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="periodo_academico" name="periodo_academico" placeholder="Ej: 2025-2025" required>
                                    <small id="error_periodo_academico" class="form-text text-danger d-none">Formato válido: 2025-2025</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="aula" class="form-label">Aula</label>
                                    <input type="text" class="form-control" id="aula" name="aula" placeholder="Ej: B1">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="nivel" class="form-label">Nivel</label>
                                    <input type="text" class="form-control" id="nivel" name="nivel" maxlength="2"  placeholder="Ej: 2">
                                    <small id="error_nivel" class="form-text text-danger d-none">Solo se aceptan números de hasta 2 dígitos</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                                    <small id="error_fecha_inicio" class="form-text text-danger d-none">Rellenar este campo</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                                    <small id="error_fecha_fin" class="form-text text-danger d-none">Rellenar este campo</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="docente_codigo" class="form-label">Código Profesor</label>
                            <select class="form-control" id="docente_codigo" name="docente_codigo" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($docentes as $doc): ?>
                                    <option value="<?= htmlspecialchars($doc['codigo']) ?>">
                                        <?= htmlspecialchars($doc['codigo']) ?> - <?= htmlspecialchars($doc['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Asignatura -->
    <div class="modal fade" id="editarAsignaturaModal" tabindex="-1" aria-labelledby="editarAsignaturaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editarAsignaturaModalLabel">
                        <i class="fas fa-edit me-2"></i>Editar Asignatura
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarAsignatura">
                    <div class="modal-body">
                        <input type="hidden" id="edit_codigo_original" name="codigo_original">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_codigo" name="codigo" required>
                                    <small id="error_edit_codigo" class="form-text text-danger d-none">Solo se aceptan números</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_nombre_asignatura" class="form-label">Asignatura <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_nombre_asignatura" name="nombre_asignatura" required placeholder="Ej: MATEMÁTICAS">
                                    <small id="error_edit_nombre_asignatura" class="form-text text-danger d-none">Solo se aceptan letras y espacios</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_horario" class="form-label">Horario</label>
                                    <input type="text" class="form-control" id="edit_horario" name="horario" placeholder="Ej: 08-10">
                                    <small id="error_edit_horario" class="form-text text-danger d-none">Formato válido: 08-10</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_jornada" class="form-label">Jornada</label>
                                    <select class="form-control" id="edit_jornada" name="jornada">
                                        <option value="">Seleccionar...</option>
                                        <option value="MATUTINA">MATUTINA</option>
                                        <option value="VESPERTINA">VESPERTINA</option>
                                        <option value="NOCTURNA">NOCTURNA</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_periodo_academico" class="form-label">Periodo Lectivo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_periodo_academico" name="periodo_academico" placeholder="Ej: 2025-2025" required>
                                    <small id="error_edit_periodo_academico" class="form-text text-danger d-none">Formato válido: 2025-2025</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_aula" class="form-label">Aula</label>
                                    <input type="text" class="form-control" id="edit_aula" name="aula">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_nivel" class="form-label">Nivel</label>
                                    <input type="text" class="form-control" id="edit_nivel" name="nivel" maxlength="2" placeholder="Ej: 2">
                                    <small id="error_edit_nivel" class="form-text text-danger d-none">Solo se aceptan números de hasta 2 dígitos</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_fecha_inicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="edit_fecha_inicio" name="fecha_inicio">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_fecha_fin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="edit_fecha_fin" name="fecha_fin">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_docente_codigo" class="form-label">Código Profesor</label>
                            <select class="form-control" id="edit_docente_codigo" name="docente_codigo" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($docentes as $doc): ?>
                                    <option value="<?= htmlspecialchars($doc['codigo']) ?>">
                                        <?= htmlspecialchars($doc['codigo']) ?> - <?= htmlspecialchars($doc['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($hayErrores && isset($_SESSION['errores_excel']) && !empty($_SESSION['errores_excel'])): ?>
        <div class="modal fade" id="erroresModal" tabindex="-1" aria-labelledby="erroresModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="erroresModalLabel">Errores encontrados</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p>Algunos registros no se pudieron procesar porque tienen campos vacíos o inválidos. Revisa los detalles:</p>
                        <div class="table-responsive">
                            <table id="tablaErroresExcel" class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Asignatura</th>
                                        <th>Horario</th>
                                        <th>Jornada</th>
                                        <th>Periodo Lectivo</th>
                                        <th>Aula</th>
                                        <th>Nivel</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th>Profesor</th>
                                        <th>Errores</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($_SESSION['errores_excel'] as $err): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($err['codigo']) ?></td>
                                            <td><?= htmlspecialchars($err['asignatura']) ?></td>
                                            <td><?= htmlspecialchars($err['horario']) ?></td>
                                            <td><?= htmlspecialchars($err['jornada']) ?></td>
                                            <td><?= htmlspecialchars($err['periodo_lectivo']) ?></td>
                                            <td><?= htmlspecialchars($err['aula']) ?></td>
                                            <td><?= htmlspecialchars($err['nivel']) ?></td>
                                            <td><?= htmlspecialchars($err['fecha_inicio']) ?></td>
                                            <td><?= htmlspecialchars($err['fecha_fin']) ?></td>
                                            <td><?= htmlspecialchars($err['profesor']) ?></td>
                                            <td class="text-danger"><?= htmlspecialchars($err['errores']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="../../app/Operaciones/descargarErroresExcel.php" class="btn btn-success">
                            <i class="fas fa-file-excel me-1"></i> Descargar errores en Excel
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
        // Validación en tiempo real para formato horario (editar)
        $('#edit_horario').on('input', function() {
            let valor = $(this).val();
            if (!/^\d{2}-\d{2}$/.test(valor)) {
                $('#edit_horario').addClass('is-invalid');
                $('#error_edit_horario').removeClass('d-none');
            } else {
                $('#edit_horario').removeClass('is-invalid');
                $('#error_edit_horario').addClass('d-none');
            }
        });
            $(document).ready(function() {
                $('#tablaErroresExcel').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                    },
                    pageLength: 5
                });
                var erroresModal = new bootstrap.Modal(document.getElementById('erroresModal'));
                erroresModal.show();
            });
        </script>
        <?php /* unset($_SESSION['errores_excel']); */ ?>
    <?php endif; ?>

    <script>
        $(document).ready(function() {
            $('#asignaturasTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                }
            });

            // Validación en tiempo real para solo números en código
            $('#codigo').on('input', function() {
                let valor = $(this).val();
                if (!/^\d*$/.test(valor)) {
                    $('#codigo').addClass('is-invalid');
                    $('#error_codigo').removeClass('d-none');
                } else {
                    $('#codigo').removeClass('is-invalid');
                    $('#error_codigo').addClass('d-none');
                }
            });

            // Validación y transformación a mayúsculas para nombre_asignatura (crear)
            $('#nombre_asignatura').on('input', function() {
                let valor = $(this).val();
                // Solo letras y espacios
                if (!/^[a-zA-ZÁÉÍÓÚáéíóúÑñ ]*$/.test(valor)) {
                    $('#nombre_asignatura').addClass('is-invalid');
                    $('#error_nombre_asignatura').removeClass('d-none');
                } else {
                    $('#nombre_asignatura').removeClass('is-invalid');
                    $('#error_nombre_asignatura').addClass('d-none');
                }
                // Transformar a mayúsculas
                $(this).val(valor.toUpperCase());
            });

            // Validación en tiempo real para formato horario (ej: 08-10)
            $('#horario').on('input', function() {
                let valor = $(this).val();
                if (!/^\d{2}-\d{2}$/.test(valor)) {
                    $('#horario').addClass('is-invalid');
                    $('#error_horario').removeClass('d-none');
                } else {
                    $('#horario').removeClass('is-invalid');
                    $('#error_horario').addClass('d-none');
                }
            });

            // Validación en tiempo real para formato periodo académico (ej: 2025-2025)
            $('#periodo_academico').on('input', function() {
                let valor = $(this).val();
                if (!/^\d{4}-\d{4}$/.test(valor)) {
                    $('#periodo_academico').addClass('is-invalid');
                    $('#error_periodo_academico').removeClass('d-none');
                } else {
                    $('#periodo_academico').removeClass('is-invalid');
                    $('#error_periodo_academico').addClass('d-none');
                }
            });

            // Validación en tiempo real para solo números de hasta 2 dígitos en nivel
            $('#nivel').on('input', function() {
                let valor = $(this).val();
                if (!/^\d{1,2}$/.test(valor)) {
                    $('#nivel').addClass('is-invalid');
                    $('#error_nivel').removeClass('d-none');
                } else {
                    $('#nivel').removeClass('is-invalid');
                    $('#error_nivel').addClass('d-none');
                }
            });

            // Función para crear nueva asignatura
            $('#formNuevaAsignatura').on('submit', function(e) {
                e.preventDefault();

                let valido = true;


                // Validación solo letras y espacios en nombre_asignatura
                let nombreVal = $('#nombre_asignatura').val();
                if (!/^[A-ZÁÉÍÓÚÑ ]+$/.test(nombreVal)) {
                    $('#nombre_asignatura').addClass('is-invalid');
                    $('#error_nombre_asignatura').removeClass('d-none');
                    valido = false;
                } else {
                    $('#nombre_asignatura').removeClass('is-invalid');
                    $('#error_nombre_asignatura').addClass('d-none');
                }

                // Validación solo números en código
                let codigoVal = $('#codigo').val();
                if (!/^\d+$/.test(codigoVal)) {
                    $('#codigo').addClass('is-invalid');
                    $('#error_codigo').removeClass('d-none');
                    valido = false;
                } else {
                    $('#codigo').removeClass('is-invalid');
                    $('#error_codigo').addClass('d-none');
                }

                // Validación solo números de hasta 2 dígitos en nivel
                let nivelVal = $('#nivel').val();
                if (!/^\d{1,2}$/.test(nivelVal)) {
                    $('#nivel').addClass('is-invalid');
                    $('#error_nivel').removeClass('d-none');
                    valido = false;
                } else {
                    $('#nivel').removeClass('is-invalid');
                    $('#error_nivel').addClass('d-none');
                }

                // Validación formato horario
                let horarioVal = $('#horario').val();
                if (horarioVal && !/^\d{2}-\d{2}$/.test(horarioVal)) {
                    $('#horario').addClass('is-invalid');
                    $('#error_horario').removeClass('d-none');
                    valido = false;
                } else {
                    $('#horario').removeClass('is-invalid');
                    $('#error_horario').addClass('d-none');
                }

                // Validación formato periodo académico
                let periodoVal = $('#periodo_academico').val();
                if (!/^\d{4}-\d{4}$/.test(periodoVal)) {
                    $('#periodo_academico').addClass('is-invalid');
                    $('#error_periodo_academico').removeClass('d-none');
                    valido = false;
                } else {
                    $('#periodo_academico').removeClass('is-invalid');
                    $('#error_periodo_academico').addClass('d-none');
                }

                // Validación de fechas
                if (!$('#fecha_inicio').val()) {
                    $('#fecha_inicio').addClass('is-invalid');
                    $('#error_fecha_inicio').removeClass('d-none');
                    valido = false;
                } else {
                    $('#fecha_inicio').removeClass('is-invalid');
                    $('#error_fecha_inicio').addClass('d-none');
                }

                if (!$('#fecha_fin').val()) {
                    $('#fecha_fin').addClass('is-invalid');
                    $('#error_fecha_fin').removeClass('d-none');
                    valido = false;
                } else {
                    $('#fecha_fin').removeClass('is-invalid');
                    $('#error_fecha_fin').addClass('d-none');
                }

                if (!valido) return;

                // Si todo está bien, sigue con el AJAX
                $.ajax({
                    url: '../../app/Operaciones/crudAsignatura.php',
                    type: 'POST',
                    data: $(this).serialize() + '&accion=crear',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#nuevaAsignaturaModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Asignatura creada correctamente',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Error al crear la asignatura'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error de conexión'
                        });
                    }
                });
            });
        });

        // Función para editar asignatura
        function editarAsignatura(codigo) {
            $.ajax({
                url: '../../app/Operaciones/crudAsignatura.php',
                type: 'POST',
                data: {
                    accion: 'obtener',
                    codigo: codigo
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#edit_codigo_original').val(data.codigo);
                        $('#edit_codigo').val(data.codigo);
                        $('#edit_nombre_asignatura').val(data.nombre_asignatura);
                        $('#edit_horario').val(data.horario);
                        let jornada = (data.jornada || '').toUpperCase().trim();
                        $('#edit_jornada option').each(function() {
                            if ($(this).val().toUpperCase().trim() === jornada) {
                                $(this).prop('selected', true);
                            }
                        });
                        $('#edit_jornada').trigger('change');
                        $('#edit_periodo_academico').val(data.periodo_academico);
                        $('#edit_aula').val(data.aula);
                        $('#edit_nivel').val(data.nivel);
                        $('#edit_fecha_inicio').val(data.fecha_inicio);
                        $('#edit_fecha_fin').val(data.fecha_fin);
                        $('#edit_docente_codigo').val(data.docente_codigo);

                        $('#editarAsignaturaModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo cargar la información'
                        });
                    }
                }
            });
        }


        // Validación y transformación a mayúsculas para nombre_asignatura (editar)
        $('#edit_nombre_asignatura').on('input', function() {
            let valor = $(this).val();
            // Solo letras y espacios
            if (!/^[a-zA-ZÁÉÍÓÚáéíóúÑñ ]*$/.test(valor)) {
                $('#edit_nombre_asignatura').addClass('is-invalid');
                $('#error_edit_nombre_asignatura').removeClass('d-none');
            } else {
                $('#edit_nombre_asignatura').removeClass('is-invalid');
                $('#error_edit_nombre_asignatura').addClass('d-none');
            }
            // Transformar a mayúsculas
            $(this).val(valor.toUpperCase());
        });

        // Validación en tiempo real para solo números en código (editar)
        $('#edit_codigo').on('input', function() {
            let valor = $(this).val();
            if (!/^\d*$/.test(valor)) {
                $('#edit_codigo').addClass('is-invalid');
                $('#error_edit_codigo').removeClass('d-none');
            } else {
                $('#edit_codigo').removeClass('is-invalid');
                $('#error_edit_codigo').addClass('d-none');
            }
        });

        // Validación en tiempo real para solo números de hasta 2 dígitos en nivel (editar)
        $('#edit_nivel').on('input', function() {
            let valor = $(this).val();
            if (!/^\d{1,2}$/.test(valor)) {
                $('#edit_nivel').addClass('is-invalid');
                $('#error_edit_nivel').removeClass('d-none');
            } else {
                $('#edit_nivel').removeClass('is-invalid');
                $('#error_edit_nivel').addClass('d-none');
            }
        });

        // Validación en tiempo real para formato periodo académico (editar)
        $('#edit_periodo_academico').on('input', function() {
            let valor = $(this).val();
            if (!/^\d{4}-\d{4}$/.test(valor)) {
                $('#edit_periodo_academico').addClass('is-invalid');
                $('#error_edit_periodo_academico').removeClass('d-none');
            } else {
                $('#edit_periodo_academico').removeClass('is-invalid');
                $('#error_edit_periodo_academico').addClass('d-none');
            }
        });

        // Forzar validación al abrir el modal de edición (por si el valor ya está mal cargado)
        $('#editarAsignaturaModal').on('shown.bs.modal', function() {
            $('#edit_periodo_academico').trigger('input');
        });

        // Función para actualizar asignatura
        $('#formEditarAsignatura').on('submit', function(e) {
            e.preventDefault();

            let valido = true;

            // Validación solo letras y espacios en nombre_asignatura (editar)
            let nombreVal = $('#edit_nombre_asignatura').val();
            if (!/^[A-ZÁÉÍÓÚÑ ]+$/.test(nombreVal)) {
                $('#edit_nombre_asignatura').addClass('is-invalid');
                $('#error_edit_nombre_asignatura').removeClass('d-none');
                valido = false;
            } else {
                $('#edit_nombre_asignatura').removeClass('is-invalid');
                $('#error_edit_nombre_asignatura').addClass('d-none');
            }

            // Validación solo números en código
            let codigoVal = $('#edit_codigo').val();
            if (!/^\d+$/.test(codigoVal)) {
                $('#edit_codigo').addClass('is-invalid');
                $('#error_edit_codigo').removeClass('d-none');
                valido = false;
            } else {
                $('#edit_codigo').removeClass('is-invalid');
                $('#error_edit_codigo').addClass('d-none');
            }

            // Validación solo números de hasta 2 dígitos en nivel
            let nivelVal = $('#edit_nivel').val();
            if (!/^\d{1,2}$/.test(nivelVal)) {
                $('#edit_nivel').addClass('is-invalid');
                $('#error_edit_nivel').removeClass('d-none');
                valido = false;
            } else {
                $('#edit_nivel').removeClass('is-invalid');
                $('#error_edit_nivel').addClass('d-none');
            }

            // Validación formato horario
            let horarioVal = $('#edit_horario').val();
            if (horarioVal && !/^\d{2}-\d{2}$/.test(horarioVal)) {
                $('#edit_horario').addClass('is-invalid');
                $('#error_edit_horario').removeClass('d-none');
                valido = false;
            } else {
                $('#edit_horario').removeClass('is-invalid');
                $('#error_edit_horario').addClass('d-none');
            }

            // Validación formato periodo académico
            let periodoVal = $('#edit_periodo_academico').val();
            if (!/^\d{4}-\d{4}$/.test(periodoVal)) {
                $('#edit_periodo_academico').addClass('is-invalid');
                $('#error_edit_periodo_academico').removeClass('d-none');
                valido = false;
            } else {
                $('#edit_periodo_academico').removeClass('is-invalid');
                $('#error_edit_periodo_academico').addClass('d-none');
            }

            if (!valido) return;

            $.ajax({
                url: '../../app/Operaciones/crudAsignatura.php',
                type: 'POST',
                data: $(this).serialize() + '&accion=actualizar',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#editarAsignaturaModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Asignatura actualizada correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Error al actualizar la asignatura'
                        });
                    }
                }
            });
        });

        // Función para eliminar asignatura
        function eliminarAsignatura(codigo, nombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas eliminar la asignatura "${nombre}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../../app/Operaciones/crudAsignatura.php',
                        type: 'POST',
                        data: {
                            accion: 'eliminar',
                            codigo: codigo
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: 'Asignatura eliminada correctamente',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Error al eliminar la asignatura'
                                });
                            }
                        }
                    });
                }
            });
        }

        // Limpiar formulario al cerrar modal
        $('#nuevaAsignaturaModal').on('hidden.bs.modal', function() {
            $('#formNuevaAsignatura')[0].reset();
        });
    </script>

    <?php if (!empty($mensaje)): ?>
        <?php
        // Determina los valores para el toast
        $toastType = '';
        $toastTitle = '';
        $toastIconColor = '';
        $toastPopupClass = '';
        $toastTimer = 4000;

        if (isset($_GET['exito'])) {
            $toastType = 'success';
            $toastTitle = 'Archivo Excel cargado correctamente.';
            $toastIconColor = '#fff';
            $toastPopupClass = 'bg-success text-white';
            $toastTimer = 4000;
        } elseif (isset($_GET['error'])) {
            $toastType = 'error';
            $toastTitle = 'Hubo un error al procesar el archivo.';
            $toastIconColor = '#fff';
            $toastPopupClass = 'bg-danger text-white';
            $toastTimer = 5000;
        } elseif (isset($_GET['error_encabezados'])) {
            $toastType = 'warning';
            $toastTitle = 'Los encabezados del archivo no son válidos. Por favor, verifica el formato.';
            $toastIconColor = '#664d03';
            $toastPopupClass = 'bg-warning text-dark';
            $toastTimer = 6000;
        } elseif (isset($_GET['error_subida'])) {
            $toastType = 'error';
            $toastTitle = 'Error al subir el archivo. Asegúrate de que sea un archivo Excel válido.';
            $toastIconColor = '#fff';
            $toastPopupClass = 'bg-danger text-white';
            $toastTimer = 6000;
        } elseif (isset($_GET['error_formato'])) {
            $toastType = 'warning';
            $toastTitle = 'El archivo subido no es un archivo Excel válido. Por favor, verifica el formato.';
            $toastIconColor = '#664d03';
            $toastPopupClass = 'bg-warning text-dark';
            $toastTimer = 6000;
        } elseif (isset($_GET['archivo_duplicado'])) {
            $toastType = 'info';
            $toastTitle = 'Este archivo ya fue subido anteriormente.';
            $toastIconColor = '#055160';
            $toastPopupClass = 'bg-info text-dark';
            $toastTimer = 5000;
        }
        ?>
        <script>
            window.toastType = "<?= $toastType ?>";
            window.toastTitle = "<?= $toastTitle ?>";
            window.toastIconColor = "<?= $toastIconColor ?>";
            window.toastPopupClass = "<?= $toastPopupClass ?>";
            window.toastTimer = <?= $toastTimer ?>;
        </script>
    <?php endif; ?>
</body>

</html>