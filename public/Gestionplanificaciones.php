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
    // Validación del lado del servidor (extra)
    $unidad = $_POST['unidad'];
    $errores = [];
    if (empty($unidad['nombre'])) $errores[] = 'nombre';
    if (empty($unidad['objetivo_unidad'])) $errores[] = 'objetivo_unidad';
    if (empty($unidad['metodologia'])) $errores[] = 'metodologia';
    if (empty($unidad['actividades_recuperacion'])) $errores[] = 'actividades_recuperacion';
    if (empty($unidad['recursos_didacticos'])) $errores[] = 'recursos_didacticos';
    if (empty($unidad['semana_inicio'])) $errores[] = 'semana_inicio';
    if (empty($unidad['semana_fin'])) $errores[] = 'semana_fin';
    if (empty($unidad['asignatura_codigo'])) $errores[] = 'asignatura_codigo';

    if (!empty($errores)) {
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errores' => $errores]);
            exit;
        }
    } else {
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
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $ok]);
            exit;
        }
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

                    info.innerHTML = '';
                    mostrarCamposUnidad(false);
                    asignaturaSeleccionadaCodigo = '';
                    asignaturaSeleccionadaNombre = '';
                });
        }

        function mostrarDatosAsignatura(valor) {
            if (!valor) {
                document.getElementById('info-asignatura').innerHTML = '';
                mostrarCamposUnidad(false);
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

            asignaturaSeleccionadaCodigo = asig.codigo || asig.codigo_asignatura || '';
            asignaturaSeleccionadaNombre = asig.nombre_asignatura || '';
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
                document.getElementById('unidad_nombre').value = '';
                if (window.quill_objetivo) quill_objetivo.setContents([]);
                if (window.quill_metodologia) quill_metodologia.setContents([]);
                if (window.quill_actividades) quill_actividades.setContents([]);
                if (window.quill_recursos) quill_recursos.setContents([]);
                document.getElementById('unidad_semana_inicio').value = '';
                document.getElementById('unidad_semana_fin').value = '';
            }
        }
    </script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white text-center">
                <h2 class="form-title mb-0">Formulario - Información General</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="formPrincipal" autocomplete="off">
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
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="nombre_docente" class="form-label">Nombre Docente</label>
                            <input type="text" name="nombre_docente" id="nombre_docente" class="form-control" readonly required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="asignatura" class="form-label">Asignatura</label>
                            <select name="asignatura" id="asignatura" class="form-select" onchange="mostrarDatosAsignatura(this.value)" required>
                                <option value="">Seleccione</option>
                            </select>
                            <div class="invalid-feedback"></div>
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
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Asignatura Código</label>
                                        <input type="text" id="unidad_asignatura" name="unidad[asignatura_codigo]" class="form-control" readonly required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Objetivo Unidad</label>
                                    <div id="quill_objetivo"></div>
                                    <div class="invalid-feedback"></div>
                                    <input type="hidden" name="unidad[objetivo_unidad]" id="unidad_objetivo" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Metodología</label>
                                    <div id="quill_metodologia"></div>
                                    <div class="invalid-feedback"></div>
                                    <input type="hidden" name="unidad[metodologia]" id="unidad_metodologia" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Actividades de Recuperación</label>
                                    <div id="quill_actividades"></div>
                                    <div class="invalid-feedback"></div>
                                    <input type="hidden" name="unidad[actividades_recuperacion]" id="unidad_actividades" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Recursos Didácticos</label>
                                    <div id="quill_recursos"></div>
                                    <div class="invalid-feedback"></div>
                                    <input type="hidden" name="unidad[recursos_didacticos]" id="unidad_recursos" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Semana Inicio</label>
                                        <input type="date" name="unidad[semana_inicio]" id="unidad_semana_inicio" class="form-control" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Semana Fin</label>
                                        <input type="date" name="unidad[semana_fin]" id="unidad_semana_fin" class="form-control" required>
                                        <div class="invalid-feedback"></div>
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
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Quill
    window.quill_objetivo = new Quill('#quill_objetivo', { theme: 'snow' });
    window.quill_metodologia = new Quill('#quill_metodologia', { theme: 'snow' });
    window.quill_actividades = new Quill('#quill_actividades', { theme: 'snow' });
    window.quill_recursos = new Quill('#quill_recursos', { theme: 'snow' });

    const form = document.getElementById('formPrincipal');
    form.addEventListener('submit', function(e) {
        let valido = true;

        // Lista de campos a validar
        const campos = [
            { id: 'docente', tipo: 'select' },
            { id: 'nombre_docente', tipo: 'input' },
            { id: 'asignatura', tipo: 'select' },
            { id: 'unidad_nombre', tipo: 'input' },
            { id: 'unidad_asignatura', tipo: 'input' },
            { id: 'unidad_objetivo', tipo: 'quill', quill: window.quill_objetivo },
            { id: 'unidad_metodologia', tipo: 'quill', quill: window.quill_metodologia },
            { id: 'unidad_actividades', tipo: 'quill', quill: window.quill_actividades },
            { id: 'unidad_recursos', tipo: 'quill', quill: window.quill_recursos },
            { id: 'unidad_semana_inicio', tipo: 'input' },
            { id: 'unidad_semana_fin', tipo: 'input' }
        ];

        // Solo validar campos de unidad si están visibles
        const unidadVisible = document.getElementById('campos-unidad').style.display === 'block';

        // Limpiar errores previos
        campos.forEach(function(campo) {
            const el = document.getElementById(campo.id);
            if (el) {
                el.classList.remove('is-invalid');
                // Para Quill, limpiar el div.invalid-feedback después del editor
                if (campo.tipo === 'quill') {
                    const feedback = el.previousElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.innerText = '';
                        feedback.style.display = 'none';
                    }
                    // Quitar borde rojo del editor Quill
                    const qlContainer = el.previousElementSibling ? el.previousElementSibling.previousElementSibling : null;
                    if (qlContainer && qlContainer.classList.contains('ql-container')) {
                        qlContainer.classList.remove('is-invalid');
                    }
                } else {
                    let msg = el.parentNode.querySelector('.invalid-feedback');
                    if (msg) {
                        msg.innerText = '';
                        msg.style.display = 'none';
                    }
                }
            }
        });

        // Validar campos
        campos.forEach(function(campo) {
            if (campo.id.startsWith('unidad_') && !unidadVisible) return;

            const el = document.getElementById(campo.id);
            let valor = '';
            if (campo.tipo === 'quill') {
                valor = campo.quill.getText().replace(/\s/g, '');
            } else if (campo.tipo === 'select') {
                valor = el.value;
            } else {
                valor = el.value.trim();
            }

            el.classList.remove('is-invalid');
            if (campo.tipo === 'quill') {
                const feedback = el.previousElementSibling;
                // El contenedor visual de Quill
                const qlContainer = el.previousElementSibling ? el.previousElementSibling.previousElementSibling : null;
                if (!valor) {
                    valido = false;
                    el.classList.add('is-invalid');
                    if (qlContainer && qlContainer.classList.contains('ql-container')) {
                        qlContainer.classList.add('is-invalid');
                    }
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.innerText = 'Llenar este campo';
                        feedback.style.display = 'block';
                    }
                } else {
                    if (qlContainer && qlContainer.classList.contains('ql-container')) {
                        qlContainer.classList.remove('is-invalid');
                    }
                }
            } else {
                let msg = el.parentNode.querySelector('.invalid-feedback');
                if (!valor) {
                    valido = false;
                    el.classList.add('is-invalid');
                    if (msg) {
                        msg.innerText = 'Llenar este campo';
                        msg.style.display = 'block';
                    }
                }
            }
        });

        if (!valido) {
            e.preventDefault();
            const primerError = document.querySelector('.is-invalid');
            if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }

        // Copiar contenido de Quill a los inputs ocultos antes de enviar
        if (unidadVisible) {
            document.getElementById('unidad_objetivo').value = quill_objetivo.root.innerHTML;
            document.getElementById('unidad_metodologia').value = quill_metodologia.root.innerHTML;
            document.getElementById('unidad_actividades').value = quill_actividades.root.innerHTML;
            document.getElementById('unidad_recursos').value = quill_recursos.root.innerHTML;
        }

        // Envío AJAX solo si hay unidad visible
        if (unidadVisible) {
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
                        var modal = new bootstrap.Modal(document.getElementById('modalExito'));
                        modal.show();
                        mostrarCamposUnidad(false);
                    } else if (data.errores) {
                        data.errores.forEach(function(campo) {
                            let inputId = '';
                            switch (campo) {
                                case 'nombre': inputId = 'unidad_nombre'; break;
                                case 'objetivo_unidad': inputId = 'unidad_objetivo'; break;
                                case 'metodologia': inputId = 'unidad_metodologia'; break;
                                case 'actividades_recuperacion': inputId = 'unidad_actividades'; break;
                                case 'recursos_didacticos': inputId = 'unidad_recursos'; break;
                                case 'semana_inicio': inputId = 'unidad_semana_inicio'; break;
                                case 'semana_fin': inputId = 'unidad_semana_fin'; break;
                                case 'asignatura_codigo': inputId = 'unidad_asignatura'; break;
                            }
                            if (inputId) {
                                const el = document.getElementById(inputId);
                                el.classList.add('is-invalid');
                                if (el.type === 'hidden') {
                                    const feedback = el.previousElementSibling;
                                    const qlContainer = el.previousElementSibling ? el.previousElementSibling.previousElementSibling : null;
                                    if (qlContainer && qlContainer.classList.contains('ql-container')) {
                                        qlContainer.classList.add('is-invalid');
                                    }
                                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                                        feedback.innerText = 'Llenar este campo';
                                        feedback.style.display = 'block';
                                    }
                                } else {
                                    let msg = el.parentNode.querySelector('.invalid-feedback');
                                    if (msg) {
                                        msg.innerText = 'Llenar este campo';
                                        msg.style.display = 'block';
                                    }
                                }
                            }
                        });
                    }
                });
        }
    });

    // Quitar error al escribir/cambiar
    [
        'docente', 'nombre_docente', 'asignatura', 'unidad_nombre', 'unidad_asignatura',
        'unidad_semana_inicio', 'unidad_semana_fin'
    ].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function() {
                el.classList.remove('is-invalid');
                let msg = el.parentNode.querySelector('.invalid-feedback');
                if (msg) {
                    msg.innerText = '';
                    msg.style.display = 'none';
                }
            });
        }
    });

    // Para selects
    ['docente', 'asignatura'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function() {
                el.classList.remove('is-invalid');
                let msg = el.parentNode.querySelector('.invalid-feedback');
                if (msg) {
                    msg.innerText = '';
                    msg.style.display = 'none';
                }
            });
        }
    });

    // Para Quill
    [
        { id: 'unidad_objetivo', quill: window.quill_objetivo },
        { id: 'unidad_metodologia', quill: window.quill_metodologia },
        { id: 'unidad_actividades', quill: window.quill_actividades },
        { id: 'unidad_recursos', quill: window.quill_recursos }
    ].forEach(function(q) {
        if (q.quill) {
            q.quill.on('text-change', function() {
                const el = document.getElementById(q.id);
                const feedback = el.previousElementSibling;
                const qlContainer = el.previousElementSibling ? el.previousElementSibling.previousElementSibling : null;
                el.classList.remove('is-invalid');
                if (qlContainer && qlContainer.classList.contains('ql-container')) {
                    qlContainer.classList.remove('is-invalid');
                }
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.innerText = '';
                    feedback.style.display = 'none';
                }
            });
        }
    });

    document.getElementById('btnAceptarModalExito').addEventListener('click', function() {
        if (asignaturaSeleccionadaCodigo && asignaturaSeleccionadaNombre) {
            const codigo = encodeURIComponent(asignaturaSeleccionadaCodigo);
            const nombre = encodeURIComponent(asignaturaSeleccionadaNombre);
            window.location.href = `gestionarReportes.php?codigo=${codigo}&nombre=${nombre}`;
        } else {
            window.location.reload();
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>