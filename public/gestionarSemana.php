<?php

$unidad_id = isset($_GET['unidad_id']) ? $_GET['unidad_id'] : '';
$unidad_nombre = isset($_GET['unidad_nombre']) ? $_GET['unidad_nombre'] : '';

require_once __DIR__ . '/../config/conexion.php';

// Obtener fechas de la unidad para limitar el rango
$fecha_inicio_unidad = '';
$fecha_fin_unidad = '';
if ($unidad_id) {
    $stmt = $pdo->prepare("SELECT semana_inicio, semana_fin FROM unidad WHERE id_unidad = ?");
    $stmt->execute([$unidad_id]);
    $unidad = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($unidad) {
        $fecha_inicio_unidad = $unidad['semana_inicio'];
        $fecha_fin_unidad = $unidad['semana_fin'];
    }
}
?>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
<link rel="stylesheet" href="../public/assets/css/gestionSemana.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<div class="alert alert-info">
    <strong>Nombre Unidad:</strong> <?php echo htmlspecialchars($unidad_nombre); ?>
</div>

<form id="formSemana" action="#" method="post" autocomplete="off">
    <input type="hidden" name="unidad_id" value="<?php echo htmlspecialchars($unidad_id); ?>">
    <div class="mb-3">
        <label class="form-label"><strong>Semana:</strong></label>
        <input type="number" class="form-control" name="semana_numero" min="1" required style="width:100px;display:inline-block;">
    </div>
    <div class="mb-3">
        <label class="form-label"><strong>Del</strong></label>
        <input type="text" class="form-control" id="semana_inicio" name="semana_inicio"
            required style="width:180px;display:inline-block;" autocomplete="off"
            data-min="<?php echo htmlspecialchars($fecha_inicio_unidad); ?>"
            data-max="<?php echo htmlspecialchars($fecha_fin_unidad); ?>">
        <label class="form-label ms-2"><strong>al</strong></label>
        <input type="text" class="form-control" id="semana_fin" name="semana_fin"
            required style="width:180px;display:inline-block;" readonly autocomplete="off"
            data-min="<?php echo htmlspecialchars($fecha_inicio_unidad); ?>"
            data-max="<?php echo htmlspecialchars($fecha_fin_unidad); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label"><strong>Actividades previas a la clase:</strong></label>
        <div id="editor_actividades_previas" class="quill-editor"></div>
        <input type="hidden" name="actividades_previas" id="input_actividades_previas">
    </div>
    <div class="mb-3">
        <label class="form-label"><strong>Tiempo (min):</strong></label>
        <input type="number" class="form-control" name="tiempo_previas" min="1" required style="width:120px;display:inline-block;">
    </div>

    <!-- Carrusel de actividades de la clase por día -->
    <div class="mb-4 row">
        <div class="col-12 col-md-4">
            <label class="form-label"><strong>Contenido:</strong></label>
            <div id="editor_contenido" class="quill-editor" style="height:350px;"></div>
            <input type="hidden" name="contenido" id="input_contenido">
        </div>
        <div class="col-12 col-md-8">
            <label class="form-label"><strong>Actividades de la clase (por día):</strong></label>
            <div id="carouselDias" class="carousel slide" data-bs-interval="false">
                <div class="carousel-inner">
                    <?php
                    $dias = [
                        'lunes' => 'Lunes',
                        'martes' => 'Martes',
                        'miercoles' => 'Miércoles',
                        'jueves' => 'Jueves',
                        'viernes' => 'Viernes'
                    ];
                    $dias_keys = array_keys($dias);
                    $i = 0;
                    foreach ($dias as $diaKey => $diaNombre): ?>
                    <div class="carousel-item<?php echo $i === 0 ? ' active' : ''; ?>">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-primary text-white position-relative p-0" style="height:60px;">
                                <div class="d-flex h-100 align-items-center justify-content-between">
                                    <!-- Botón izquierda -->
                                    <div style="width:60px;" class="h-100 d-flex align-items-center justify-content-center">
                                        <?php if ($i > 0): ?>
                                        <button class="btn custom-carousel-btn" type="button" data-bs-target="#carouselDias" data-bs-slide="prev">
                                            <span class="bi bi-arrow-left-circle-fill fs-2 text-white"></span>
                                        </button>
                                        <?php elseif ($i === 0): // Lunes, mostrar volver a viernes ?>
                                        <button class="btn custom-carousel-btn" type="button" data-bs-target="#carouselDias" data-bs-slide="prev">
                                            <span class="bi bi-arrow-left-circle-fill fs-2 text-white"></span>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Día centrado -->
                                    <div class="flex-grow-1 text-center">
                                        <strong style="font-size:1.3rem;"><?php echo $diaNombre; ?></strong>
                                    </div>
                                    <!-- Botón derecha -->
                                    <div style="width:60px;" class="h-100 d-flex align-items-center justify-content-center">
                                        <?php if ($i < count($dias) - 1): ?>
                                        <button class="btn custom-carousel-btn" type="button" data-bs-target="#carouselDias" data-bs-slide="next">
                                            <span class="bi bi-arrow-right-circle-fill fs-2 text-white"></span>
                                        </button>
                                        <?php elseif ($i === count($dias) - 1): // Viernes, mostrar ir a lunes ?>
                                        <button class="btn custom-carousel-btn" type="button" data-bs-target="#carouselDias" data-bs-slide="next">
                                            <span class="bi bi-arrow-right-circle-fill fs-2 text-white"></span>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Objetivo:</strong>
                                    <div id="editor_objetivo_<?php echo $diaKey; ?>" class="quill-editor" style="height:60px;"></div>
                                    <input type="hidden" name="objetivo_<?php echo $diaKey; ?>" id="input_objetivo_<?php echo $diaKey; ?>">
                                </div>
                                <div class="mb-2">
                                    <strong>Apertura:</strong>
                                    <div class="mb-1">
                                        <strong>Tiempo:</strong>
                                        <input type="number" class="form-control" name="tiempo_apertura_<?php echo $diaKey; ?>" min="1" style="width:90px;display:inline-block;" placeholder="min">
                                    </div>
                                    <div id="editor_apertura_<?php echo $diaKey; ?>" class="quill-editor" style="height:60px;"></div>
                                    <input type="hidden" name="apertura_<?php echo $diaKey; ?>" id="input_apertura_<?php echo $diaKey; ?>">
                                </div>
                                <div class="mb-2">
                                    <strong>Desarrollo:</strong>
                                    <div class="mb-1">
                                        <strong>Tiempo:</strong>
                                        <input type="number" class="form-control" name="tiempo_desarrollo_<?php echo $diaKey; ?>" min="1" style="width:90px;display:inline-block;" placeholder="min">
                                    </div>
                                    <div id="editor_desarrollo_<?php echo $diaKey; ?>" class="quill-editor" style="height:60px;"></div>
                                    <input type="hidden" name="desarrollo_<?php echo $diaKey; ?>" id="input_desarrollo_<?php echo $diaKey; ?>">
                                </div>
                                <div class="mb-2">
                                    <strong>Cierre:</strong>
                                    <div class="mb-1">
                                        <strong>Tiempo:</strong>
                                        <input type="number" class="form-control" name="tiempo_cierre_<?php echo $diaKey; ?>" min="1" style="width:90px;display:inline-block;" placeholder="min">
                                    </div>
                                    <div id="editor_cierre_<?php echo $diaKey; ?>" class="quill-editor" style="height:60px;"></div>
                                    <input type="hidden" name="cierre_<?php echo $diaKey; ?>" id="input_cierre_<?php echo $diaKey; ?>">
                                </div>
                                <div class="mb-2">
                                    <strong>Trabajo autónomo:</strong>
                                    <div id="editor_trabajo_autonomo_<?php echo $diaKey; ?>" class="quill-editor" style="height:60px;"></div>
                                    <input type="hidden" name="trabajo_autonomo_<?php echo $diaKey; ?>" id="input_trabajo_autonomo_<?php echo $diaKey; ?>">
                                </div>
                                <div class="mb-2">
                                    <strong>Fecha de entrega:</strong>
                                    <input type="text" class="form-control fecha-entrega" name="entrega_<?php echo $diaKey; ?>" autocomplete="off"
                                        data-min="<?php echo htmlspecialchars($fecha_inicio_unidad); ?>"
                                        data-max="<?php echo htmlspecialchars($fecha_fin_unidad); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $i++; endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Semana</button>
    <div id="semanaSuccess" class="alert alert-success mt-3" style="display:none;">
        <i class="bi bi-check-circle-fill"></i> Semana guardada correctamente.
    </div>
</form>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
<script>
const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
dias.forEach(dia => {
    ['objetivo', 'apertura', 'desarrollo', 'cierre', 'trabajo_autonomo'].forEach(tipo => {
        new Quill(`#editor_${tipo}_${dia}`, { theme: 'snow', placeholder: `Escriba ${tipo.replace('_', ' ')}...` });
    });
});
var quill_contenido = new Quill('#editor_contenido', { theme: 'snow', placeholder: 'Describa el contenido...' });
var quill_actividades_previas = new Quill('#editor_actividades_previas', { theme: 'snow', placeholder: 'Describa las actividades previas...' });
</script>




