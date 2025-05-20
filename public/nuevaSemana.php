<?php
$id_unidad = isset($_GET['id_unidad']) ? intval($_GET['id_unidad']) : 0;
?>
<form id="formAgregarSemana">
    <input type="hidden" name="id_unidad" id="semana_id_unidad" value="<?= $id_unidad ?>">
    <div class="mb-3">
        <label class="form-label">Fecha de la Semana</label>
        <input type="text" class="form-control" name="fecha_semana" id="fecha_semana" required autocomplete="off">
        <div id="info_fecha_viernes" class="form-text text-success mt-2" style="font-weight:bold;"></div>
    </div>
    <div class="mb-3">
        <label class="form-label">Actividades Previas</label>
        <div id="quill_actividades_previas" class="quill-editor"></div>
        <input type="hidden" name="actividades_previas" id="input_actividades_previas">
    </div>
    <div class="mb-3">
        <label class="form-label">Contenido</label>
        <div id="quill_contenido" class="quill-editor"></div>
        <input type="hidden" name="contenido" id="input_contenido">
    </div>

    <!-- Acordeón para los días de la semana -->
    <div class="accordion mb-3" id="accordionDiasSemana">
        <?php
        $dias = [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes'
        ];
        foreach ($dias as $dia_key => $dia_nombre):
        ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading<?= $dia_key ?>">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $dia_key ?>" aria-expanded="false" aria-controls="collapse<?= $dia_key ?>">
                    <span id="label_<?= $dia_key ?>"><?= $dia_nombre ?></span>
                </button>
            </h2>
            <div id="collapse<?= $dia_key ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $dia_key ?>" data-bs-parent="#accordionDiasSemana">
                <div class="accordion-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="fecha_<?= $dia_key ?>" id="fecha_<?= $dia_key ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Fecha Entrega</label>
                            <input type="date" class="form-control" name="fecha_entrega_<?= $dia_key ?>" id="fecha_entrega_<?= $dia_key ?>" readonly>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Objetivo</label>
                        <div id="quill_objetivo_<?= $dia_key ?>" class="quill-editor"></div>
                        <input type="hidden" name="objetivo_<?= $dia_key ?>" id="input_objetivo_<?= $dia_key ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tiempo Objetivo</label>
                        <div id="quill_tiempo_objetivo_<?= $dia_key ?>" class="quill-editor"></div>
                        <input type="hidden" name="tiempo_objetivo_<?= $dia_key ?>" id="input_tiempo_objetivo_<?= $dia_key ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Apertura</label>
                        <div id="quill_apertura_<?= $dia_key ?>" class="quill-editor"></div>
                        <input type="hidden" name="apertura_<?= $dia_key ?>" id="input_apertura_<?= $dia_key ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tiempo Apertura</label>
                        <div id="quill_tiempo_apertura_<?= $dia_key ?>" class="quill-editor"></div>
                        <input type="hidden" name="tiempo_apertura_<?= $dia_key ?>" id="input_tiempo_apertura_<?= $dia_key ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Desarrollo</label>
                        <div id="quill_desarrollo_<?= $dia_key ?>" class="quill-editor"></div>
                        <input type="hidden" name="desarrollo_<?= $dia_key ?>" id="input_desarrollo_<?= $dia_key ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tiempo Desarrollo</label>
                        <div id="quill_tiempo_desarrollo_<?= $dia_key ?>" class="quill-editor"></div>
                        <input type="hidden" name="tiempo_desarrollo_<?= $dia_key ?>" id="input_tiempo_desarrollo_<?= $dia_key ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Cierre</label>
                        <div id="quill_cierre_<?= $dia_key ?>" class="quill-editor"></div>
                        <input type="hidden" name="cierre_<?= $dia_key ?>" id="input_cierre_<?= $dia_key ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tiempo Cierre</label>
                        <div id="quill_tiempo_cierre_<?= $dia_key ?>" class="quill-editor"></div>
                        <input type="hidden" name="tiempo_cierre_<?= $dia_key ?>" id="input_tiempo_cierre_<?= $dia_key ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Trabajo Autónomo</label>
                        <div id="quill_trabajo_autonomo_<?= $dia_key ?>" class="quill-editor"></div>
                        <input type="hidden" name="trabajo_autonomo_<?= $dia_key ?>" id="input_trabajo_autonomo_<?= $dia_key ?>">
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success">Guardar Semana</button>
    </div>
</form>