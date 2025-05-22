<?php
$unidad_id = isset($_GET['unidad_id']) ? $_GET['unidad_id'] : '';
$unidad_nombre = isset($_GET['unidad_nombre']) ? $_GET['unidad_nombre'] : '';

// Función para generar el formulario de semana
function getSemanaFormHtml($semanaIndex = 1) {
    ob_start(); ?>
    <div class="card mb-3 semana-card" data-semana="<?php echo $semanaIndex; ?>">
        <div class="card-header bg-info text-white">
            <strong>Semana <?php echo $semanaIndex; ?></strong>
        </div>
        <div class="card-body">
            <form class="formSemana">
                <div class="mb-2">
                    <label>Del: <input type="date" name="semana_inicio" required></label>
                    <label class="ms-2">al: <input type="date" name="semana_fin" required></label>
                </div>
                <div class="mb-2">
                    <label>Actividades previas a la clase:</label>
                    <div id="editor_actividades_previas_semana<?php echo $semanaIndex; ?>" class="quill-editor"></div>
                    <input type="hidden" name="actividades_previas" class="input_actividades_previas">
                </div>
                <div class="mb-2">
                    <label>Tiempo (min):</label>
                    <input type="number" name="tiempo_previas" class="form-control" required>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Contenido</th>
                                <th>Lunes</th>
                                <th>Martes</th>
                                <th>Miércoles</th>
                                <th>Jueves</th>
                                <th>Viernes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td rowspan="4">
                                    <div id="editor_contenido_semana<?php echo $semanaIndex; ?>" class="quill-editor"></div>
                                    <input type="hidden" name="contenido" class="input_contenido">
                                </td>
                                <?php foreach(['Lunes','Martes','Miércoles','Jueves','Viernes'] as $dia): ?>
                                <td>
                                    <strong>Objetivo:</strong>
                                    <div id="editor_objetivo_<?php echo $dia; ?>_semana<?php echo $semanaIndex; ?>" class="quill-editor mb-1"></div>
                                    <input type="hidden" name="objetivo_<?php echo $dia; ?>" class="input_objetivo_<?php echo $dia; ?>">
                                    <strong>Apertura:</strong>
                                    <div id="editor_apertura_<?php echo $dia; ?>_semana<?php echo $semanaIndex; ?>" class="quill-editor mb-1"></div>
                                    <input type="hidden" name="apertura_<?php echo $dia; ?>" class="input_apertura_<?php echo $dia; ?>">
                                    <small>Tiempo:</small>
                                    <input type="number" name="tiempo_apertura_<?php echo $dia; ?>" class="form-control mb-1">
                                    <strong>Desarrollo:</strong>
                                    <div id="editor_desarrollo_<?php echo $dia; ?>_semana<?php echo $semanaIndex; ?>" class="quill-editor mb-1"></div>
                                    <input type="hidden" name="desarrollo_<?php echo $dia; ?>" class="input_desarrollo_<?php echo $dia; ?>">
                                    <small>Tiempo:</small>
                                    <input type="number" name="tiempo_desarrollo_<?php echo $dia; ?>" class="form-control mb-1">
                                    <strong>Cierre:</strong>
                                    <div id="editor_cierre_<?php echo $dia; ?>_semana<?php echo $semanaIndex; ?>" class="quill-editor mb-1"></div>
                                    <input type="hidden" name="cierre_<?php echo $dia; ?>" class="input_cierre_<?php echo $dia; ?>">
                                    <small>Tiempo:</small>
                                    <input type="number" name="tiempo_cierre_<?php echo $dia; ?>" class="form-control mb-1">
                                    <strong>Trabajo autónomo:</strong>
                                    <div id="editor_autonomo_<?php echo $dia; ?>_semana<?php echo $semanaIndex; ?>" class="quill-editor mb-1"></div>
                                    <input type="hidden" name="autonomo_<?php echo $dia; ?>" class="input_autonomo_<?php echo $dia; ?>">
                                    <strong>Fecha de entrega:</strong>
                                    <input type="date" name="fecha_entrega_<?php echo $dia; ?>" class="form-control mb-1">
                                </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm btnQuitarSemana">Quitar semana</button>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<div class="alert alert-info">
    <strong>ID Unidad:</strong> <?php echo htmlspecialchars($unidad_id); ?><br>
    <strong>Nombre Unidad:</strong> <?php echo htmlspecialchars($unidad_nombre); ?>
</div>

<!-- Contenedor de semanas -->
<div id="semanasContainer">
    <?php echo getSemanaFormHtml(1); ?>
</div>
<button type="button" class="btn btn-success mb-3" id="btnAgregarSemanaModal">
    <i class="bi bi-calendar-plus"></i> Agregar otra semana
</button>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
function initQuillSemana(semanaIndex) {
    const quillOptions = {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'font': [] }, { 'size': [] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    };
    // Actividades previas
    new Quill(`#editor_actividades_previas_semana${semanaIndex}`, quillOptions);
    // Contenido
    new Quill(`#editor_contenido_semana${semanaIndex}`, quillOptions);
    // Por cada día y campo
    ['Lunes','Martes','Miércoles','Jueves','Viernes'].forEach(dia => {
        new Quill(`#editor_objetivo_${dia}_semana${semanaIndex}`, quillOptions);
        new Quill(`#editor_apertura_${dia}_semana${semanaIndex}`, quillOptions);
        new Quill(`#editor_desarrollo_${dia}_semana${semanaIndex}`, quillOptions);
        new Quill(`#editor_cierre_${dia}_semana${semanaIndex}`, quillOptions);
        new Quill(`#editor_autonomo_${dia}_semana${semanaIndex}`, quillOptions);
    });
}

// Inicializa la primera semana
initQuillSemana(1);

// Agregar nueva semana
document.getElementById('btnAgregarSemanaModal').addEventListener('click', function() {
    const semanasContainer = document.getElementById('semanasContainer');
    const semanaIndex = semanasContainer.children.length + 1;
    // Generar el HTML de la nueva semana usando AJAX o directamente en PHP
    fetch(window.location.pathname + '?unidad_id=<?php echo urlencode($unidad_id); ?>&unidad_nombre=<?php echo urlencode($unidad_nombre); ?>&form_semana=1&semanaIndex=' + semanaIndex)
        .then(res => res.text())
        .then(html => {
            // Si usas AJAX para obtener solo el HTML de la semana, reemplaza esto por el HTML generado
            // Aquí, para simplicidad, lo generamos en JS:
            // semanasContainer.insertAdjacentHTML('beforeend', html);
            // Pero como estamos en el mismo archivo, usamos PHP:
            semanasContainer.insertAdjacentHTML('beforeend', `<?php echo str_replace(array("\r","\n"), '', getSemanaFormHtml('"+semanaIndex+"')); ?>`);
            initQuillSemana(semanaIndex);
        });
});

// Botón para quitar la semana
document.getElementById('semanasContainer').addEventListener('click', function(e) {
    if (e.target.classList.contains('btnQuitarSemana')) {
        e.target.closest('.card').remove();
    }
});
</script>