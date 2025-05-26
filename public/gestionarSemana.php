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
<link rel = "stylesheet" href="../public/assets/css/gestionSemana.css ">

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

    <div class="mb-4">
        <table class="table table-bordered align-middle" style="background: #fff;">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 14%; text-align:center; vertical-align:middle;"><strong>Contenido</strong></th>
                    <th colspan="5" style="text-align:center;"><strong>Actividades de la clase</strong></th>
                </tr>
                <tr>
                    <th style="width: 17%; text-align:center;">Lunes</th>
                    <th style="width: 17%; text-align:center;">Martes</th>
                    <th style="width: 17%; text-align:center;">Miércoles</th>
                    <th style="width: 17%; text-align:center;">Jueves</th>
                    <th style="width: 17%; text-align:center;">Viernes</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td rowspan="7">
                        <div id="editor_contenido" class="quill-editor" style="height:350px;"></div>
                        <input type="hidden" name="contenido" id="input_contenido">
                    </td>
                    <?php
                    $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
                    foreach ($dias as $dia) {
                        echo '<td>
                            <strong>Objetivo:</strong>
                            <div id="editor_objetivo_' . $dia . '" class="quill-editor" style="height:60px;"></div>
                            <input type="hidden" name="objetivo_' . $dia . '" id="input_objetivo_' . $dia . '">
                        </td>';
                    }
                    ?>
                </tr>
                <tr>
                    <?php foreach ($dias as $dia): ?>
                        <td>
                            <strong>Apertura:</strong>
                            <div class="mt-1 mb-1">
                                <strong>Tiempo:</strong>
                                <input type="number" class="form-control" name="tiempo_apertura_<?php echo $dia; ?>" min="1" style="width:90px;display:inline-block;" placeholder="min">
                            </div>
                            <div id="editor_apertura_<?php echo $dia; ?>" class="quill-editor" style="height:60px;"></div>
                            <input type="hidden" name="apertura_<?php echo $dia; ?>" id="input_apertura_<?php echo $dia; ?>">
                        </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($dias as $dia): ?>
                        <td>
                            <strong>Desarrollo:</strong>
                            <div class="mt-1 mb-1">
                                <strong>Tiempo:</strong>
                                <input type="number" class="form-control" name="tiempo_desarrollo_<?php echo $dia; ?>" min="1" style="width:90px;display:inline-block;" placeholder="min">
                            </div>
                            <div id="editor_desarrollo_<?php echo $dia; ?>" class="quill-editor" style="height:60px;"></div>
                            <input type="hidden" name="desarrollo_<?php echo $dia; ?>" id="input_desarrollo_<?php echo $dia; ?>">
                        </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($dias as $dia): ?>
                        <td>
                            <strong>Cierre:</strong>
                            <div class="mt-1 mb-1">
                                <strong>Tiempo:</strong>
                                <input type="number" class="form-control" name="tiempo_cierre_<?php echo $dia; ?>" min="1" style="width:90px;display:inline-block;" placeholder="min">
                            </div>
                            <div id="editor_cierre_<?php echo $dia; ?>" class="quill-editor" style="height:60px;"></div>
                            <input type="hidden" name="cierre_<?php echo $dia; ?>" id="input_cierre_<?php echo $dia; ?>">
                        </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($dias as $dia): ?>
                        <td>
                            <strong>Trabajo autónomo:</strong>
                            <div id="editor_trabajo_autonomo_<?php echo $dia; ?>" class="quill-editor" style="height:60px;"></div>
                            <input type="hidden" name="trabajo_autonomo_<?php echo $dia; ?>" id="input_trabajo_autonomo_<?php echo $dia; ?>">
                        </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($dias as $dia): ?>
                        <td>
                            <strong>Fecha de entrega:</strong>
                            <input type="text" class="form-control fecha-entrega" name="entrega_<?php echo $dia; ?>" autocomplete="off"
                                data-min="<?php echo htmlspecialchars($fecha_inicio_unidad); ?>"
                                data-max="<?php echo htmlspecialchars($fecha_fin_unidad); ?>">
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Semana</button>
    <div id="semanaSuccess" class="alert alert-success mt-3" style="display:none;">
        <i class="bi bi-check-circle-fill"></i> Semana guardada correctamente.
    </div>
</form>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
