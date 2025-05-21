<?php
require_once __DIR__ . '/../config/conexion.php';

$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';

$asignatura = null;
$docente_nombre = '';

if ($codigo) {
    // Obtener datos de la asignatura y el docente
    $stmt = $pdo->prepare("SELECT a.*, d.nombre AS docente_nombre 
        FROM asignatura a 
        LEFT JOIN docente d ON a.docente_codigo = d.codigo 
        WHERE a.codigo = ?");
    $stmt->execute([$codigo]);
    $asignatura = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($asignatura) {
        $docente_nombre = $asignatura['docente_nombre'];
    }
}
?>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .quill-editor {
        min-height: 90px;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
        margin-bottom: 8px;
    }
    .table th, .table td {
        vertical-align: top;
    }
</style>
<div class="modal-header">
    <h5 class="modal-title" id="modalNuevaUnidadLabel">Crear Nueva Unidad</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>
<div class="modal-body">
    <?php if ($asignatura): ?>
    <!-- Encabezado informativo -->
    <table class="table table-bordered align-middle mb-4" style="background: #fff;">
        <tr style="background: #ffff00;">
            <td><strong>Asignatura:</strong></td>
            <td><?php echo htmlspecialchars($asignatura['nombre_asignatura']); ?></td>
            <td><strong>Código de la asignatura:</strong></td>
            <td><?php echo htmlspecialchars($asignatura['codigo']); ?></td>
        </tr>
        <tr style="background: #ffff00;">
            <td><strong>Modalidad:</strong></td>
            <td><?php echo htmlspecialchars(strtoupper($asignatura['jornada'])); ?></td>
            <td><strong>Nivel:</strong></td>
            <td><?php echo htmlspecialchars($asignatura['nivel']); ?></td>
            <td><strong>Docente:</strong></td>
            <td><?php echo htmlspecialchars($docente_nombre); ?></td>
        </tr>
    </table>

    <!-- Formulario de ingreso de unidad -->
    <form id="formNuevaUnidad">
        <input type="hidden" name="asignatura_codigo" value="<?php echo htmlspecialchars($codigo); ?>">

        <!-- Tabla editable para los datos de la unidad -->
        <table class="table table-bordered" style="background: #fff;">
            <tr>
                <td colspan="4" style="text-align:center; background:#eaeaea;">
                    <strong>Unidad N°</strong>
                    <input type="number" name="numero_unidad" min="1" style="width:60px; display:inline-block;" required>
                    &nbsp;&nbsp;<strong>Nombre:</strong>
                    <input type="text" name="nombre" style="width:40%;" required>
                </td>
            </tr>
            <tr>
                <!-- Objetivo de la unidad -->
                <td style="width:30%; vertical-align:top;">
                    <strong>Objetivo de la unidad:</strong>
                    <div id="editor_objetivo" class="quill-editor"></div>
                    <input type="hidden" name="objetivo_unidad" id="input_objetivo_unidad">
                    <br>
                    <strong>Bibliografía:</strong>
                    <div id="editor_bibliografia" class="quill-editor"></div>
                    <input type="hidden" name="bibliografia" id="input_bibliografia">
                </td>
                <!-- Metodología -->
                <td style="width:20%; vertical-align:top;">
                    <strong>Metodologías de evaluación de la unidad:</strong>
                    <div id="editor_metodologia" class="quill-editor"></div>
                    <input type="hidden" name="metodologia" id="input_metodologia">
                </td>
                <!-- Actividades de recuperación -->
                <td style="width:20%; vertical-align:top;">
                    <strong>Actividades de recuperación de la unidad:</strong>
                    <div id="editor_actividades" class="quill-editor"></div>
                    <input type="hidden" name="actividades_recuperacion" id="input_actividades_recuperacion">
                </td>
                <!-- Recursos didácticos -->
                <td style="width:30%; vertical-align:top;">
                    <strong>Equipo/Herramienta/Recursos didácticos de la unidad:</strong>
                    <div id="editor_recursos" class="quill-editor"></div>
                    <input type="hidden" name="recursos_didacticos" id="input_recursos_didacticos">
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <strong>Semana Inicio:</strong>
                    <input type="date" name="semana_inicio" required>
                    &nbsp;&nbsp;
                    <strong>Semana Fin:</strong>
                    <input type="date" name="semana_fin" required>
                </td>
            </tr>
        </table>
        <div class="text-end">
            <button type="submit" class="btn btn-primary">Guardar Unidad</button>
        </div>
    </form>
    <?php else: ?>
        <div class="alert alert-danger">No se encontró la asignatura seleccionada.</div>
    <?php endif; ?>
</div>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    // Inicializar los editores Quill
    var quill_objetivo = new Quill('#editor_objetivo', { theme: 'snow', placeholder: 'Escriba el objetivo de la unidad...' });
    var quill_bibliografia = new Quill('#editor_bibliografia', { theme: 'snow', placeholder: 'Ingrese la bibliografía...' });
    var quill_metodologia = new Quill('#editor_metodologia', { theme: 'snow', placeholder: 'Describa la metodología...' });
    var quill_actividades = new Quill('#editor_actividades', { theme: 'snow', placeholder: 'Describa las actividades de recuperación...' });
    var quill_recursos = new Quill('#editor_recursos', { theme: 'snow', placeholder: 'Describa los recursos didácticos...' });

    // Al enviar el formulario, pasar el contenido de Quill a los inputs ocultos
    document.getElementById('formNuevaUnidad').addEventListener('submit', function(e) {
        e.preventDefault();
        document.getElementById('input_objetivo_unidad').value = quill_objetivo.root.innerHTML;
        document.getElementById('input_bibliografia').value = quill_bibliografia.root.innerHTML;
        document.getElementById('input_metodologia').value = quill_metodologia.root.innerHTML;
        document.getElementById('input_actividades_recuperacion').value = quill_actividades.root.innerHTML;
        document.getElementById('input_recursos_didacticos').value = quill_recursos.root.innerHTML;

        // Enviar por AJAX a la ruta correcta
        var formData = new FormData(this);
        fetch('/sysplanificacion/app/createUnidad.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                alert('Unidad guardada correctamente');
                location.reload();
            }else{
                alert('Error al guardar la unidad: ' + (data.message || ''));
            }
        })
        .catch(err => {
            alert('Error en la conexión o en el servidor.');
        });
    });
</script>