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
    <style>
        .form-container {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .section-title {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .form-group-custom {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .btn-generate {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 25px;
            color: white;
            transition: all 0.3s;
        }
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="form-container">
                    <!-- Título principal -->
                    <div class="section-title text-center">
                        <h2><i class="fas fa-clipboard-list me-2"></i>Generación de Planificación de Coevaluaciones</h2>
                    </div>

                    <form id="formCoevaluacion" method="POST" action="">
                        <!-- Fecha del documento -->
                        <div class="form-group-custom">
                            <h5 class="mb-3"><i class="fas fa-calendar me-2 text-primary"></i>Fecha del Documento</h5>
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
                            <h5 class="mb-3"><i class="fas fa-user me-2 text-primary"></i>Datos del Coordinador (DE:)</h5>
                            <div class="alert alert-info">
                                <strong><?php echo $coordinadorLogueado['titulo_abreviado']; ?></strong><br>
                                <?php echo htmlspecialchars($coordinadorLogueado['nombre']); ?><br>
                                Coordinador de Carrera de <?php echo htmlspecialchars($coordinadorLogueado['carrera']); ?>
                            </div>
                        </div>

                        <!-- Modalidad -->
                        <div class="form-group-custom">
                            <h5 class="mb-3"><i class="fas fa-graduation-cap me-2 text-primary"></i>Modalidad de Estudio</h5>
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
                            <h5 class="mb-3"><i class="fas fa-calendar-alt me-2 text-primary"></i>Período de Coevaluaciones</h5>
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

                        <!-- Botón para generar -->
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-generate btn-lg" onclick="generarPDF()">
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
                    <button type="button" class="btn btn-primary" onclick="descargarPDF()">
                        <i class="fas fa-download me-2"></i>Descargar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validar fechas
        document.getElementById('fecha_fin').addEventListener('change', function() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = this.value;
            
            if (fechaInicio && fechaFin && fechaFin <= fechaInicio) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                this.value = '';
            }
        });

        function generarPDF() {
            // Validar formulario
            const form = document.getElementById('formCoevaluacion');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Obtener datos del formulario
            const formData = new FormData(form);
            formData.append('generar_pdf', '1');

            // Crear URL para el PDF - RUTA CORREGIDA
            const params = new URLSearchParams(formData);
            const pdfUrl = '../../app/CoevaluacionBack/coevaluacionPDF.php?' + params.toString();
            
            // Mostrar en modal
            document.getElementById('pdfViewer').src = pdfUrl;
            new bootstrap.Modal(document.getElementById('pdfModal')).show();
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