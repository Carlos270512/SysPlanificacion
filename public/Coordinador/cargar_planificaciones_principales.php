<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'COORDINADOR') {
    header("Location: ../index.php");
    exit();
}

// Conexión a la base de datos
$pdo = require_once __DIR__ . '/../../config/conexion.php';

// Obtener todos los docentes activos
$stmt = $pdo->prepare("SELECT codigo, nombre, carrera FROM docente WHERE estado = 'ACTIVO' ORDER BY nombre ASC");
$stmt->execute();
$docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Planificaciones Principales</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #fff;
        }
        .header-section {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .docente-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .asignatura-item {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .asignatura-item:hover {
            background-color: #f8f9fa;
        }
        .badge-custom {
            font-size: 0.85rem;
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Header -->
        <div class="header-section">
            <h2 class="mb-0">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                Cargar Planificaciones Principales
            </h2>
            <p class="text-muted mb-0 mt-2">Seleccione un docente para ver sus asignaturas</p>
        </div>

        <!-- Selector de Docente -->
        <div class="row mb-4">
            <div class="col-md-10">
                <label for="selectDocente" class="form-label fw-bold">
                    <i class="fas fa-user me-2"></i>Seleccionar Docente:
                </label>
                <select class="form-select" id="selectDocente">
                    <option value="">-- Seleccione un docente --</option>
                    <?php foreach ($docentes as $docente): ?>
                        <option value="<?php echo htmlspecialchars($docente['codigo']); ?>">
                            <?php echo htmlspecialchars($docente['codigo'] . ' - ' . $docente['nombre'] . ' (' . $docente['carrera'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Información del Docente Seleccionado -->
        <div id="docenteInfo" style="display: none;">
            <div class="docente-card">
                <h5 class="mb-3">
                    <i class="fas fa-user-circle me-2"></i>
                    Información del Docente
                </h5>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Código:</strong> <span id="docCodigo"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Nombre:</strong> <span id="docNombre"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Carrera:</strong> <span id="docCarrera"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Asignaturas -->
        <div id="asignaturasContainer" style="display: none;">
            <h4 class="mb-3">
                <i class="fas fa-book me-2"></i>
                Asignaturas del Docente
                <span class="badge bg-secondary" id="totalAsignaturas">0</span>
            </h4>
            <div id="listaAsignaturas"></div>
        </div>

        <!-- Mensaje cuando no hay asignaturas -->
        <div id="sinAsignaturas" style="display: none;">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                El docente seleccionado no tiene asignaturas asignadas.
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#selectDocente').on('change', function() {
                const codigoDocente = $(this).val();
                
                if (!codigoDocente) {
                    $('#docenteInfo').hide();
                    $('#asignaturasContainer').hide();
                    $('#sinAsignaturas').hide();
                    return;
                }

                // Obtener información del docente seleccionado
                const textoSeleccionado = $(this).find('option:selected').text();
                const partes = textoSeleccionado.split(' - ');
                const codigo = partes[0];
                const nombreYCarrera = partes[1].split(' (');
                const nombre = nombreYCarrera[0];
                const carrera = nombreYCarrera[1].replace(')', '');

                // Mostrar información del docente
                $('#docCodigo').text(codigo);
                $('#docNombre').text(nombre);
                $('#docCarrera').text(carrera);
                $('#docenteInfo').show();

                // Cargar asignaturas mediante AJAX
                cargarAsignaturas(codigoDocente);
            });
        });

        function cargarAsignaturas(codigoDocente) {
            $.ajax({
                url: '../../app/GestionPlanificaciones.php/getAsignaturasPorDocente.php',
                method: 'GET',
                data: { codigo_docente: codigoDocente },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.asignaturas.length > 0) {
                        mostrarAsignaturas(response.asignaturas);
                    } else {
                        $('#asignaturasContainer').hide();
                        $('#sinAsignaturas').show();
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar las asignaturas'
                    });
                }
            });
        }

        function mostrarAsignaturas(asignaturas) {
            $('#sinAsignaturas').hide();
            $('#totalAsignaturas').text(asignaturas.length);
            
            let html = '';
            asignaturas.forEach(function(asig, index) {
                html += `
                    <div class="asignatura-item">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h6 class="mb-2">
                                    <i class="fas fa-book-open me-2"></i>
                                    ${asig.nombre_asignatura}
                                </h6>
                                <div class="d-flex gap-3 flex-wrap">
                                    <small><strong>Código:</strong> ${asig.codigo}</small>
                                    <small><strong>Nivel:</strong> ${asig.nivel}</small>
                                    <small><strong>Jornada:</strong> ${asig.jornada}</small>
                                    <small><strong>Aula:</strong> ${asig.aula}</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <small class="text-muted">${asig.periodo_academico}</small><br>
                                <small class="text-muted">${asig.horario}</small>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-sm btn-primary" onclick="abrirPlanificacionBase('${asig.codigo}')" title="Gestionar Planificación Base">
                                    <i class="fas fa-edit me-1"></i>Gestionar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            $('#listaAsignaturas').html(html);
            $('#asignaturasContainer').show();
        }

        function abrirPlanificacionBase(codigoAsignatura) {
            // Redirigir a crearPlanificaciones.php con el código de la asignatura
            window.parent.location.href = '../crearPlanificaciones.php?codigo=' + codigoAsignatura + '&unidad_base=1';
        }
    </script>
</body>
</html>
