<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Conexión a la base de datos
$pdo = require_once __DIR__ . '/../config/conexion.php';

// 1. Obtener el correo del usuario logueado
$correo = $_SESSION['usuario']['correo'];

// 2. Buscar el código y carrera del docente por correo
$stmt = $pdo->prepare("SELECT codigo, carrera FROM docente WHERE correo = ?");
$stmt->execute([$correo]);
$docente = $stmt->fetch(PDO::FETCH_ASSOC);

$asignaturas = [];
if ($docente) {
    // 3. Buscar todas las asignaturas del docente
    $stmt2 = $pdo->prepare("SELECT * FROM asignatura WHERE docente_codigo = ?");
    $stmt2->execute([$docente['codigo']]);
    $asignaturas = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Planificaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <div class="container mt-4">
        <h2>Bienvenido, <?php echo $_SESSION['usuario']['nombre']; ?></h2>
        <p><strong>Correo:</strong> <?php echo $_SESSION['usuario']['correo']; ?></p>
        <p><strong>Rol:</strong> <?php echo $_SESSION['usuario']['rol']; ?></p>

        <?php if ($asignaturas && count($asignaturas) > 0): ?>
            <form>
                <div class="mb-3">
                    <label for="asignatura" class="form-label">Seleccione una asignatura:</label>
                    <select class="form-select" id="asignatura" name="asignatura">
                        <?php foreach ($asignaturas as $asig): ?>
                            <option value="<?php echo htmlspecialchars($asig['codigo']); ?>">
                                <?php echo htmlspecialchars($asig['nombre_asignatura']) . " - " . htmlspecialchars($asig['codigo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            <div id="asignaturaCard" class="card mb-4" style="display:none;">
                <div class="card-body" id="asignaturaCardBody"></div>
            </div>
            <!-- Botón verde "Generar Unidad" debajo de los datos de la asignatura -->
            <div id="btnUnidadContainer" class="mb-3" style="display:none;">
                <button class="btn btn-success" id="btnGenerarUnidad">
                    <i class="bi bi-plus-circle"></i> Generar Unidad
                </button>
            </div>
            <!-- Modal grande para Nueva Unidad -->
            <div class="modal fade" id="modalNuevaUnidad" tabindex="-1" aria-labelledby="modalNuevaUnidadLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content" id="modalNuevaUnidadContent">
                        <!-- Aquí se cargará el formulario dinámicamente -->
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No tienes asignaturas asignadas.</div>
        <?php endif; ?>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const asignaturas = <?php echo json_encode($asignaturas); ?>;
            const carrera = <?php echo json_encode($docente ? $docente['carrera'] : ''); ?>;
            const select = document.getElementById('asignatura');
            const card = document.getElementById('asignaturaCard');
            const cardBody = document.getElementById('asignaturaCardBody');
            const btnUnidadContainer = document.getElementById('btnUnidadContainer');

            select.addEventListener('change', function() {
                const codigo = this.value;
                const asig = asignaturas.find(a => a.codigo === codigo);
                if (asig) {
                    card.style.display = 'block';
                    cardBody.innerHTML = `
                        <h5 class="card-title mb-3">${asig.nombre_asignatura}</h5>
                        <div class="table-responsive">
                        <table class="asig-table">
                            <tr>
                                <td><strong>Código:</strong> ${asig.codigo}</td>
                                <td><strong>Nivel:</strong> ${asig.nivel}</td>
                                <td><strong>Jornada:</strong> ${asig.jornada}</td>
                                <td><strong>Modalidad:</strong> ${asig.jornada}</td>
                            </tr>
                            <tr>
                                <td><strong>Aula:</strong> ${asig.aula}</td>
                                <td><strong>Carrera:</strong> ${carrera}</td>
                                <td><strong>Horario:</strong> ${asig.horario}</td>
                                <td><strong>Fecha inicio:</strong> ${asig.fecha_inicio ? (new Date(asig.fecha_inicio)).toLocaleDateString() : ''}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha fin:</strong> ${asig.fecha_fin ? (new Date(asig.fecha_fin)).toLocaleDateString() : ''}</td>
                            </tr>
                        </table>
                        </div>
                    `;
                    btnUnidadContainer.style.display = 'block';
                } else {
                    card.style.display = 'none';
                    btnUnidadContainer.style.display = 'none';
                }
            });

            // Mostrar la card de la primera asignatura por defecto si existe
            if (select.value) {
                select.dispatchEvent(new Event('change'));
            }

            // Evento para el botón "Generar Unidad" con inicialización de QuillJS después de cargar el modal
            document.getElementById('btnGenerarUnidad').addEventListener('click', function() {
                const select = document.getElementById('asignatura');
                const selectedOption = select.options[select.selectedIndex];
                const codigo = selectedOption.value;
                const nombre = selectedOption.text;

                fetch(`modalNuevaUnidad.php?codigo=${encodeURIComponent(codigo)}&nombre=${encodeURIComponent(nombre)}`)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('modalNuevaUnidadContent').innerHTML = html;
                        var modal = new bootstrap.Modal(document.getElementById('modalNuevaUnidad'));
                        modal.show();

                        // Esperar a que el DOM del modal esté listo
                        setTimeout(function() {
                            // Inicializar los editores Quill SOLO si existen los divs
                            if (document.getElementById('editor_objetivo')) {
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

                                    var formData = new FormData(this);
                                    fetch('/sysplanificacion/app/createUnidad.php', { // <--- CORREGIDO AQUÍ
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
                            }
                        }, 300); // Espera breve para asegurar que el DOM del modal esté listo
                    });
            });
        });
    </script>
</body>
</html>