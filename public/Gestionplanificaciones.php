<?php
$pdo = require_once __DIR__ . '/../config/conexion.php';

// 1. Obtener asignaturas dinámicamente si se pide con AJAX
if (isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];
    // Si se pide solo el nombre del docente
    if (isset($_GET['get_nombre']) && $_GET['get_nombre'] == '1') {
        $stmt = $pdo->prepare("SELECT nombre FROM docente WHERE codigo = ?");
        $stmt->execute([$codigo]);
        $docente = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($docente);
        exit;
    }
    $stmt = $pdo->prepare("
        SELECT a.*, d.carrera 
        FROM asignatura a
        INNER JOIN docente d ON a.docente_codigo = d.codigo
        WHERE d.codigo = ?
    ");
    $stmt->execute([$codigo]);
    $asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($asignaturas);
    exit;
}

// 2. Procesar envío del formulario principal (PDF o guardar unidad)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unidad']['nombre'], $_POST['asignatura'])) {
    // Guardar la unidad en la base de datos
    $unidad = $_POST['unidad'];
    $stmt = $pdo->prepare("INSERT INTO unidad (nombre, objetivo_unidad, metodologia, actividades_recuperacion, recursos_didacticos, semana_inicio, semana_fin, asignatura_codigo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $ok = $stmt->execute([
        $unidad['nombre'],
        $unidad['objetivo_unidad'],
        $unidad['metodologia'],
        $unidad['actividades_recuperacion'],
        $unidad['recursos_didacticos'],
        $unidad['semana_inicio'],
        $unidad['semana_fin'],
        $unidad['asignatura_codigo']
    ]);
    // Si se quiere generar PDF, puedes redirigir o manejar aquí
    // Por ahora, solo devolvemos JSON si es AJAX
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit;
    }
    // Si no es AJAX, puedes redirigir o mostrar mensaje
}

// 3. Mostrar formulario
$stmt = $pdo->prepare("SELECT codigo, nombre FROM docente");
$stmt->execute();
$docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información General</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/gestionPlanificacionesStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script>
        // Variables globales para asignatura seleccionada
        let asignaturaSeleccionadaCodigo = '';
        let asignaturaSeleccionadaNombre = '';

        function cargarNombreDocente(codigo) {
            if (!codigo) {
                document.getElementById('nombre_docente').value = '';
                return;
            }
            fetch('Gestionplanificaciones.php?codigo=' + codigo + '&get_nombre=1')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('nombre_docente').value = data && data.nombre ? data.nombre : '';
                });
        }

        function cargarAsignaturas(docenteCodigo) {
            cargarNombreDocente(docenteCodigo);
            if (!docenteCodigo) return;

            fetch('Gestionplanificaciones.php?codigo=' + docenteCodigo)
                .then(response => response.json())
                .then(data => {
                    const selectAsignatura = document.getElementById('asignatura');
                    const info = document.getElementById('info-asignatura');
                    selectAsignatura.innerHTML = '<option value="">Seleccione</option>';

                    data.forEach(asig => {
                        const option = document.createElement('option');
                        option.value = JSON.stringify(asig);
                        option.textContent = asig.nombre_asignatura;
                        selectAsignatura.appendChild(option);
                    });

                    info.innerHTML = ''; // Limpiar si cambia el docente
                    mostrarCamposUnidad(false);
                    // Limpiar variables globales
                    asignaturaSeleccionadaCodigo = '';
                    asignaturaSeleccionadaNombre = '';
                });
        }

        function mostrarDatosAsignatura(valor) {
            if (!valor) {
                document.getElementById('info-asignatura').innerHTML = '';
                mostrarCamposUnidad(false);
                // Limpiar variables globales
                asignaturaSeleccionadaCodigo = '';
                asignaturaSeleccionadaNombre = '';
                return;
            }
            const asig = JSON.parse(valor);

            document.getElementById('info-asignatura').innerHTML = `  
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Carrera:</label>
                        <input type="text" name="carrera" value="${asig.carrera}" class="form-control" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jornada:</label>
                        <input type="text" name="jornada" value="${asig.jornada}" class="form-control" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Horario:</label>
                        <input type="text" name="horario" value="${asig.horario}" class="form-control" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nivel:</label>
                        <input type="text" name="nivel" value="${asig.nivel}" class="form-control" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Aula:</label>
                        <input type="text" name="aula" value="${asig.aula}" class="form-control" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha Inicio:</label>
                        <input type="text" value="${asig.fecha_inicio}" class="form-control" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha Fin:</label>
                        <input type="text" value="${asig.fecha_fin}" class="form-control" readonly>
                    </div>
                </div>
            `;

            // Guardar en variables globales
            asignaturaSeleccionadaCodigo = asig.codigo || asig.codigo_asignatura || '';
            asignaturaSeleccionadaNombre = asig.nombre_asignatura || '';

            // Mostrar campos de unidad y poner el código de asignatura
            mostrarCamposUnidad(true, asignaturaSeleccionadaCodigo);
        }

        function mostrarCamposUnidad(mostrar, codigoAsignatura = '') {
            const unidad = document.getElementById('campos-unidad');
            if (mostrar) {
                unidad.style.display = 'block';
                document.getElementById('unidad_asignatura').value = codigoAsignatura;
            } else {
                unidad.style.display = 'none';
                document.getElementById('unidad_asignatura').value = '';
                // Limpiar campos
                document.getElementById('unidad_nombre').value = '';
                // Limpiar Quill
                if (window.quill_objetivo) quill_objetivo.setContents([]);
                if (window.quill_metodologia) quill_metodologia.setContents([]);
                if (window.quill_actividades) quill_actividades.setContents([]);
                if (window.quill_recursos) quill_recursos.setContents([]);
                document.getElementById('unidad_semana_inicio').value = '';
                document.getElementById('unidad_semana_fin').value = '';
            }
        }

        // Enviar formulario por AJAX y mostrar modal de éxito
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar Quill
            window.quill_objetivo = new Quill('#quill_objetivo', { theme: 'snow' });
            window.quill_metodologia = new Quill('#quill_metodologia', { theme: 'snow' });
            window.quill_actividades = new Quill('#quill_actividades', { theme: 'snow' });
            window.quill_recursos = new Quill('#quill_recursos', { theme: 'snow' });

            const form = document.getElementById('formPrincipal');
            form.addEventListener('submit', function(e) {
                // Solo enviar por AJAX si hay unidad (campos de unidad visibles)
                if (document.getElementById('campos-unidad').style.display === 'block') {
                    // Copiar contenido de Quill a los inputs ocultos
                    document.getElementById('unidad_objetivo').value = quill_objetivo.root.innerHTML;
                    document.getElementById('unidad_metodologia').value = quill_metodologia.root.innerHTML;
                    document.getElementById('unidad_actividades').value = quill_actividades.root.innerHTML;
                    document.getElementById('unidad_recursos').value = quill_recursos.root.innerHTML;

                    e.preventDefault();
                    const formData = new FormData(form);
                    fetch('', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Mostrar modal de éxito
                                var modal = new bootstrap.Modal(document.getElementById('modalExito'));
                                modal.show();
                                // Limpiar campos de unidad
                                mostrarCamposUnidad(false);
                            }
                        });
                }
            });

            // Redirigir al dar click en "Aceptar" del modal
            document.getElementById('btnAceptarModalExito').addEventListener('click', function() {
                if (asignaturaSeleccionadaCodigo && asignaturaSeleccionadaNombre) {
                    // Codificar parámetros para URL
                    const codigo = encodeURIComponent(asignaturaSeleccionadaCodigo);
                    const nombre = encodeURIComponent(asignaturaSeleccionadaNombre);
                    window.location.href = `gestionarReportes.php?codigo=${codigo}&nombre=${nombre}`;
                } else {
                    // Si por alguna razón no hay datos, solo recarga la página
                    window.location.reload();
                }
            });
        });
    </script>
    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white text-center">
                <h2 class="form-title mb-0">Formulario - Información General</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="formPrincipal">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" readonly>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="docente" class="form-label">Código Docente</label>
                            <select name="docente" id="docente" class="form-select form-select-md" onchange="cargarAsignaturas(this.value)" required>
                                <option value="">Seleccione código</option>
                                <?php foreach ($docentes as $docente): ?>
                                    <option value="<?= $docente['codigo'] ?>"><?= $docente['codigo'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="nombre_docente" class="form-label">Nombre Docente</label>
                            <input type="text" name="nombre_docente" id="nombre_docente" class="form-control" readonly required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="asignatura" class="form-label">Asignatura</label>
                            <select name="asignatura" id="asignatura" class="form-select" onchange="mostrarDatosAsignatura(this.value)" required>
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                    </div>

                    <div id="info-asignatura"></div>

                    <!-- Campos de Unidad (se muestran solo si hay asignatura seleccionada) -->
                    <div id="campos-unidad" style="display:none;">
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">Datos de la Unidad</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nombre Unidad</label>
                                        <input type="text" name="unidad[nombre]" id="unidad_nombre" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Asignatura Código</label>
                                        <input type="text" id="unidad_asignatura" name="unidad[asignatura_codigo]" class="form-control" readonly required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Objetivo Unidad</label>
                                    <div id="quill_objetivo"></div>
                                    <input type="hidden" name="unidad[objetivo_unidad]" id="unidad_objetivo" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Metodología</label>
                                    <div id="quill_metodologia"></div>
                                    <input type="hidden" name="unidad[metodologia]" id="unidad_metodologia" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Actividades de Recuperación</label>
                                    <div id="quill_actividades"></div>
                                    <input type="hidden" name="unidad[actividades_recuperacion]" id="unidad_actividades" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Recursos Didácticos</label>
                                    <div id="quill_recursos"></div>
                                    <input type="hidden" name="unidad[recursos_didacticos]" id="unidad_recursos" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Semana Inicio</label>
                                        <input type="date" name="unidad[semana_inicio]" id="unidad_semana_inicio" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Semana Fin</label>
                                        <input type="date" name="unidad[semana_fin]" id="unidad_semana_fin" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Fin campos unidad -->

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Guardar Unidad y/o Generar PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<!-- Modal de éxito -->
<div class="modal fade" id="modalExito" tabindex="-1" aria-labelledby="modalExitoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalExitoLabel">¡Éxito!</h5>
                <!-- Botón de cerrar (X) eliminado para que solo se pueda cerrar con "Aceptar" -->
            </div>
            <div class="modal-body">
                Unidad generada correctamente.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnAceptarModalExito" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Deshabilita todos los enlaces y botones fuera del modal cuando el modal está abierto
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('modalExito');
        var modal = new bootstrap.Modal(modalEl);

        modalEl.addEventListener('show.bs.modal', function () {
            // Deshabilitar todos los enlaces y botones fuera del modal
            document.querySelectorAll('a, button').forEach(function(el) {
                if (!modalEl.contains(el) && el.id !== 'btnAceptarModalExito') {
                    el.setAttribute('data-prev-disabled', el.disabled);
                    el.disabled = true;
                    el.classList.add('disabled');
                    if (el.tagName === 'A') {
                        el.setAttribute('data-prev-href', el.getAttribute('href'));
                        el.removeAttribute('href');
                    }
                }
            });
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            // Restaurar enlaces y botones
            document.querySelectorAll('a, button').forEach(function(el) {
                if (!modalEl.contains(el) && el.hasAttribute('data-prev-disabled')) {
                    el.disabled = (el.getAttribute('data-prev-disabled') === 'true');
                    el.classList.remove('disabled');
                    el.removeAttribute('data-prev-disabled');
                    if (el.tagName === 'A' && el.hasAttribute('data-prev-href')) {
                        el.setAttribute('href', el.getAttribute('data-prev-href'));
                        el.removeAttribute('data-prev-href');
                    }
                }
            });
        });
        
    });
</script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>