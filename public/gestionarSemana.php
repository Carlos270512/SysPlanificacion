<?php
$unidad_id = isset($_GET['unidad_id']) ? $_GET['unidad_id'] : '';
$unidad_nombre = isset($_GET['unidad_nombre']) ? $_GET['unidad_nombre'] : '';
?>
<style>
    textarea.form-control {
        min-height: 90px;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
        margin-bottom: 8px;
        resize: vertical;
    }
</style>
<div class="alert alert-info">
    <strong>ID Unidad:</strong> <?php echo htmlspecialchars($unidad_id); ?><br>
    <strong>Nombre Unidad:</strong> <?php echo htmlspecialchars($unidad_nombre); ?>
</div>

<form id="formSemana" action="#" method="post" autocomplete="off">
    <div class="mb-3">
        <label class="form-label"><strong>Semana:</strong></label>
        <input type="number" class="form-control" name="semana_numero" min="1" required style="width:100px;display:inline-block;">
    </div>
    <div class="mb-3">
        <label class="form-label"><strong>Del</strong></label>
        <input type="date" class="form-control" name="fecha_inicio" required style="width:180px;display:inline-block;">
        <label class="form-label ms-2"><strong>al</strong></label>
        <input type="date" class="form-control" name="fecha_fin" required style="width:180px;display:inline-block;">
    </div>
    <div class="mb-3">
        <label class="form-label"><strong>Actividades previas a la clase:</strong></label>
        <textarea class="form-control" name="actividades_previas" rows="4" required placeholder="Describa las actividades previas a la clase..."></textarea>
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
                        <textarea class="form-control" name="contenido" rows="18" placeholder="Describa el contenido de la semana..."></textarea>
                    </td>
                    <?php
                    $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
                    foreach ($dias as $dia) {
                        echo '<td>
                            <strong>Objetivo:</strong>
                            <textarea class="form-control" name="objetivo_'.$dia.'" rows="2" placeholder="Objetivo..."></textarea>
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
                        <textarea class="form-control" name="apertura_<?php echo $dia; ?>" rows="2" placeholder="Apertura..."></textarea>
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
                        <textarea class="form-control" name="desarrollo_<?php echo $dia; ?>" rows="2" placeholder="Desarrollo..."></textarea>
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
                        <textarea class="form-control" name="cierre_<?php echo $dia; ?>" rows="2" placeholder="Cierre..."></textarea>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($dias as $dia): ?>
                    <td>
                        <strong>Trabajo autónomo:</strong>
                        <textarea class="form-control" name="trabajo_autonomo_<?php echo $dia; ?>" rows="2" placeholder="Trabajo autónomo..."></textarea>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($dias as $dia): ?>
                    <td>
                        <strong>Fecha de entrega:</strong>
                        <input type="date" class="form-control" name="fecha_entrega_<?php echo $dia; ?>">
                    </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Semana</button>
</form>