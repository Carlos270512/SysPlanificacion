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
    <link rel="stylesheet" href="assets/css/SemanaPlanificacionstyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
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

        <!-- TABLA DE SEMANAS Y BOTÓN NUEVA SEMANA -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Semanas de esta unidad</h5>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnNuevaSemana">
                    + Nueva semana
                </button>
            </div>
            <div id="tablaSemanasUnidad"></div>
        </div>
        <!-- FIN TABLA DE SEMANAS -->

        <!-- INICIO ACORDEÓN -->
        <div class="accordion" id="acordeonPlanificacion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingPlanificacion">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePlanificacion" aria-expanded="true" aria-controls="collapsePlanificacion">
                        Planificación semanal (mostrar/ocultar)
                    </button>
                </h2>
                <div id="collapsePlanificacion" class="accordion-collapse collapse show" aria-labelledby="headingPlanificacion" data-bs-parent="#acordeonPlanificacion">
                    <div class="accordion-body">
                        <!-- FORMULARIO ORIGINAL -->
                        <form id="formSemana" method="post" action="/SysPlanificacion/app/Semana/createSemana.php">
                            <input type="hidden" name="unidad_id" value="<?php echo htmlspecialchars($id_unidad); ?>">
                            <input type="hidden" name="id_semana" id="id_semana" value="">
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="fw-bold fs-5 me-2">Semana:</span>
                                        <span class="fw-semibold fs-5 me-2">Del</span>
                                        <input type="date" name="semana_inicio" id="semana_inicio" class="form-control form-control-sm w-auto me-2" required>
                                        <span class="fw-semibold fs-5 me-2">al</span>
                                        <input type="date" name="semana_fin" id="semana_fin" class="form-control form-control-sm w-auto" readonly>
                                    </div>
                                    <div class="mb-2">
                                        <label class="fw-bold fs-6 mb-1" style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px;">
                                            Actividades previas a la clase:
                                        </label>
                                        <div id="editor_actividades_previas" class="quill-editor border rounded bg-white"></div>
                                        <input type="hidden" name="actividades_previas" required>
                                    </div>
                                    <div class="d-flex align-items-center mt-2">
                                        <label class="fw-bold me-2">Tiempo:</label>
                                        <input type="text" name="tiempo_previas" class="form-control form-control-sm w-auto" placeholder="min">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Columna de Contenido fija -->
                                <div class="col-md-4">
                                    <label class="resaltado">Contenido:</label>
                                    <div id="editor_contenido" class="quill-editor"></div>
                                    <input type="hidden" name="contenido" required>
                                </div>
                                <!-- Columna de Carrusel de Días -->
                                <div class="col-md-8">
                                    <!-- Tabs de días -->
                                    <ul class="nav nav-tabs mb-2" id="diasTabs" role="tablist">
                                        <?php
                                        $dias = [
                                            'lunes' => 'Lunes',
                                            'martes' => 'Martes',
                                            'miercoles' => 'Miércoles',
                                            'jueves' => 'Jueves',
                                            'viernes' => 'Viernes'
                                        ];
                                        $first = true;
                                        foreach ($dias as $dia_key => $dia_nombre): ?>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link<?php if ($first) echo ' active'; ?>" id="tab-<?php echo $dia_key; ?>" data-bs-toggle="tab" data-bs-target="#dia-<?php echo $dia_key; ?>" type="button" role="tab" aria-controls="dia-<?php echo $dia_key; ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
                                                    <?php echo $dia_nombre; ?>
                                                </button>
                                            </li>
                                        <?php $first = false;
                                        endforeach; ?>
                                    </ul>
                                    <!-- Contenido de cada día -->
                                    <div class="tab-content" id="diasTabsContent">
                                        <?php
                                        $campos = ['objetivo', 'apertura', 'desarrollo', 'cierre', 'trabajo_autonomo'];
                                        $first = true;
                                        foreach ($dias as $dia_key => $dia_nombre): ?>
                                            <div class="tab-pane fade<?php if ($first) echo ' show active'; ?>" id="dia-<?php echo $dia_key; ?>" role="tabpanel" aria-labelledby="tab-<?php echo $dia_key; ?>">
                                                <div class="card border">
                                                    <div class="card-body">
                                                        <div class="mb-2">
                                                            <strong>Objetivo:</strong>
                                                            <div id="editor_objetivo_<?php echo $dia_key; ?>" class="quill-editor"></div>
                                                            <input type="hidden" name="objetivo_<?php echo $dia_key; ?>">
                                                            <div class="mt-1">
                                                                <label>Tiempo:</label>
                                                                <input type="text" name="tiempo_objetivo_<?php echo $dia_key; ?>" style="width:60px;" placeholder="min">
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Apertura:</strong>
                                                            <div id="editor_apertura_<?php echo $dia_key; ?>" class="quill-editor"></div>
                                                            <input type="hidden" name="apertura_<?php echo $dia_key; ?>">
                                                            <div class="mt-1">
                                                                <label>Tiempo:</label>
                                                                <input type="text" name="tiempo_apertura_<?php echo $dia_key; ?>" style="width:60px;" placeholder="min">
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Desarrollo:</strong>
                                                            <div id="editor_desarrollo_<?php echo $dia_key; ?>" class="quill-editor"></div>
                                                            <input type="hidden" name="desarrollo_<?php echo $dia_key; ?>">
                                                            <div class="mt-1">
                                                                <label>Tiempo:</label>
                                                                <input type="text" name="tiempo_desarrollo_<?php echo $dia_key; ?>" style="width:60px;" placeholder="min">
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Cierre:</strong>
                                                            <div id="editor_cierre_<?php echo $dia_key; ?>" class="quill-editor"></div>
                                                            <input type="hidden" name="cierre_<?php echo $dia_key; ?>">
                                                            <div class="mt-1">
                                                                <label>Tiempo:</label>
                                                                <input type="text" name="tiempo_cierre_<?php echo $dia_key; ?>" style="width:60px;" placeholder="min">
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Trabajo autónomo:</strong>
                                                            <div id="editor_trabajo_autonomo_<?php echo $dia_key; ?>" class="quill-editor"></div>
                                                            <input type="hidden" name="trabajo_autonomo_<?php echo $dia_key; ?>">
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Fecha de entrega:</strong>
                                                            <input type="date" name="entrega_<?php echo $dia_key; ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php $first = false;
                                        endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-primary" id="btnGuardarSemana">Guardar Semana</button>
                                <button type="button" class="btn btn-success ms-2" id="btnVisualizarPDF" disabled>Visualizar PDF</button>
                                <a href="crearPlanificaciones.php?codigo=<?php echo urlencode($codigo); ?>&volver=1&id_unidad=<?php echo urlencode($id_unidad); ?>" class="btn btn-secondary ms-2">
                                    &larr; Atrás
                                </a>
                            </div>
                        </form>
                        <!-- FIN FORMULARIO ORIGINAL -->
                    </div>
                </div>
            </div>
        </div>
        <!-- FIN ACORDEÓN -->

    </div>
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script src="/SysPlanificacion/public/assets/js/Semana/crearSemana.js"></script>
    <script src="/SysPlanificacion/public/assets/js/Semana/crearSemanaPlanificacion.js"></script>
    <!-- Nuevo JS para gestión de semanas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/SysPlanificacion/public/assets/js/Semana/gestionSemanasUnidad.js"></script>
</body>

</html>