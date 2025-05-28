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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
    <script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0"></script>
    <link rel="stylesheet" href="assets/css/planificaciontyle.css">
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
                <div id="btnUnidadContainer" class="mb-3" style="display:none;">
                    <button class="btn btn-success" id="btnGenerarUnidad">
                        <i class="bi bi-plus-circle"></i> Generar Unidad
                    </button>
                </div>
            </div>
            <!-- Carrusel de Unidades Generadas -->
            <div id="carruselUnidadesContainer" class="mb-4" style="display:none;">
                <h5>Unidades Generadas</h5>
                <!-- Buscador de unidades -->
                <div class="mb-4" id="buscadorCarruselContainer" style="display:none;">
                    <input type="text" id="buscadorCarrusel" class="form-control w-18" placeholder="Buscar unidad por nombre...">
                </div>
                <div style="display: flex; align-items: center;">
                    <button id="btnCarruselIzq" class="btn btn-light btn-sm me-2" style="height: 60px; width: 40px; display: none;">
                        <i class="bi bi-chevron-left fs-3"></i>
                    </button>
                    <div id="carruselUnidades" style="overflow: hidden; width: 100%;">
                        <div id="carruselUnidadesInner" style="display: flex; gap: 24px; transition: transform 0.3s;"></div>
                    </div>
                    <button id="btnCarruselDer" class="btn btn-light btn-sm ms-2" style="height: 60px; width: 40px; display: none;">
                        <i class="bi bi-chevron-right fs-3"></i>
                    </button>
                </div>
            </div>
            <!-- Modal para ver/editar unidad -->
            <div class="modal fade" id="modalEditarUnidad" tabindex="-1" aria-labelledby="modalEditarUnidadLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content" id="modalEditarUnidadContent"></div>
                </div>
            </div>
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
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
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
    <table class="table align-middle">
        <tr>
            <td class="px-3"><strong>Código:</strong><br>${asig.codigo}</td>
            <td class="px-3"><strong>Nivel:</strong><br>${asig.nivel}</td>
            <td class="px-3"><strong>Jornada:</strong><br>${asig.jornada}</td>
            <td class="px-3"><strong>Modalidad:</strong><br>${asig.jornada}</td>
        </tr>
        <tr>
            <td class="px-3"><strong>Aula:</strong><br>${asig.aula}</td>
            <td class="px-3"><strong>Carrera:</strong><br>${carrera}</td>
            <td class="px-3"><strong>Horario:</strong><br>${asig.horario}</td>
            <td class="px-3"><strong>Fecha inicio:</strong><br>${asig.fecha_inicio ? (new Date(asig.fecha_inicio)).toLocaleDateString() : ''}</td>
        </tr>
        <tr>
            <td class="px-3"><strong>Fecha fin:</strong><br>${asig.fecha_fin ? (new Date(asig.fecha_fin)).toLocaleDateString() : ''}</td>
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

        // Evento para el botón "Generar Unidad"
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
                        // Inicializar los editores Quill SOLO si existen los divs de unidad
                        if (document.getElementById('editor_objetivo')) {
                            var quill_objetivo = new Quill('#editor_objetivo', {
                                theme: 'snow',
                                placeholder: 'Escriba el objetivo de la unidad...'
                            });
                            var quill_bibliografia = new Quill('#editor_bibliografia', {
                                theme: 'snow',
                                placeholder: 'Ingrese la bibliografía...'
                            });
                            var quill_metodologia = new Quill('#editor_metodologia', {
                                theme: 'snow',
                                placeholder: 'Describa la metodología...'
                            });
                            var quill_actividades = new Quill('#editor_actividades', {
                                theme: 'snow',
                                placeholder: 'Describa las actividades de recuperación...'
                            });
                            var quill_recursos = new Quill('#editor_recursos', {
                                theme: 'snow',
                                placeholder: 'Describa los recursos didácticos...'
                            });

                            // --- INICIO MODIFICACIÓN: Inicializar Pikaday solo para el modal de unidad ---
                            if (document.getElementById('semana_inicio')) {
                                var pickerInicio = new Pikaday({
                                    field: document.getElementById('semana_inicio'),
                                    format: 'YYYY-MM-DD',
                                    disableDayFn: function(date) {
                                        // Solo lunes
                                        return date.getDay() !== 1;
                                    },
                                    toString(date, format) {
                                        const day = ("0" + date.getDate()).slice(-2);
                                        const month = ("0" + (date.getMonth() + 1)).slice(-2);
                                        return date.getFullYear() + '-' + month + '-' + day;
                                    }
                                });
                            }
                            if (document.getElementById('semana_fin')) {
                                var pickerFin = new Pikaday({
                                    field: document.getElementById('semana_fin'),
                                    format: 'YYYY-MM-DD',
                                    disableDayFn: function(date) {
                                        return date.getDay() !== 1;
                                    },
                                    toString(date, format) {
                                        const day = ("0" + date.getDate()).slice(-2);
                                        const month = ("0" + (date.getMonth() + 1)).slice(-2);
                                        return date.getFullYear() + '-' + month + '-' + day;
                                    }
                                });
                            }
                            // --- FIN MODIFICACIÓN ---

                            document.getElementById('formNuevaUnidad').addEventListener('submit', function(e) {
                                e.preventDefault();
                                document.getElementById('input_objetivo_unidad').value = quill_objetivo.root.innerHTML;
                                document.getElementById('input_bibliografia').value = quill_bibliografia.root.innerHTML;
                                document.getElementById('input_metodologia').value = quill_metodologia.root.innerHTML;
                                document.getElementById('input_actividades_recuperacion').value = quill_actividades.root.innerHTML;
                                document.getElementById('input_recursos_didacticos').value = quill_recursos.root.innerHTML;

                                var formData = new FormData(this);
                                fetch('/sysplanificacion/app/Unidad/createUnidad.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            if (document.getElementById('unidadSuccess')) {
                                                document.getElementById('unidadSuccess').style.display = 'block';
                                            }
                                            if (document.getElementById('btnNuevaSemana')) {
                                                document.getElementById('btnNuevaSemana').disabled = false;
                                            }
                                            if (document.getElementById('formNuevaUnidad')) {
                                                Array.from(document.querySelectorAll('#formNuevaUnidad input, #formNuevaUnidad button')).forEach(el => {
                                                    if (el.id !== 'btnNuevaSemana') el.disabled = true;
                                                });
                                            }
                                            window.unidadId = data.unidad_id;
                                            window.unidadNombre = document.querySelector('[name="nombre"]').value;

                                            // --- AGREGADO: Recargar el carrusel automáticamente ---
                                            const select = document.getElementById('asignatura');
                                            if (select && select.value) {
                                                cargarUnidades(select.value);
                                            }
                                            // --- FIN AGREGADO ---
                                        } else {
                                            alert('Error al guardar la unidad: ' + (data.message || ''));
                                        }
                                    })
                                    .catch(err => {
                                        alert('Error en la conexión o en el servidor.');
                                    });
                            });

                            // Evento para el botón Nueva Semana
                            document.getElementById('btnNuevaSemana').addEventListener('click', function() {
                                if (!window.unidadId) return;
                                fetch(`/sysplanificacion/public/gestionarSemana.php?unidad_id=${window.unidadId}&unidad_nombre=${encodeURIComponent(window.unidadNombre)}`)
                                    .then(res => res.text())
                                    .then(html => {
                                        document.getElementById('modalUnidadBody').innerHTML = html;
                                        setTimeout(function() {
                                            inicializarQuillSemana();
                                            if (window.inicializarPikadayEntrega) window.inicializarPikadayEntrega();
                                        }, 200);
                                    });
                            });
                        }
                    }, 300); // Espera breve para asegurar que el DOM del modal esté listo
                });
        });

        // Función global para inicializar Quill en gestionarSemana.php
        window.inicializarQuillSemana = function() {
            if (typeof Quill === 'undefined') return;
            if (!document.getElementById('formSemana')) return;

            // --- INICIO MODIFICACIÓN: Inicializar Pikaday para los campos de fecha de semana ---
            var semanaInicioInput = document.getElementById('semana_inicio');
            var semanaFinInput = document.getElementById('semana_fin');
            var minDate = semanaInicioInput && semanaInicioInput.dataset.min ? new Date(semanaInicioInput.dataset.min) : null;
            var maxDateRaw = semanaInicioInput && semanaInicioInput.dataset.max ? new Date(semanaInicioInput.dataset.max) : null;
            var maxDate = maxDateRaw;

            // Calcular el último lunes válido para semana_inicio
            if (minDate && maxDateRaw) {
                var lastMonday = new Date(maxDateRaw);
                lastMonday.setDate(lastMonday.getDate() - ((lastMonday.getDay() + 6) % 7));
                var fridayOfThatWeek = new Date(lastMonday);
                fridayOfThatWeek.setDate(lastMonday.getDate() + 4);
                if (fridayOfThatWeek > maxDateRaw) {
                    lastMonday.setDate(lastMonday.getDate() - 7);
                }
                maxDate = lastMonday;
            }

            if (semanaInicioInput) {
                var pickerSemanaInicio = new Pikaday({
                    field: semanaInicioInput,
                    format: 'YYYY-MM-DD',
                    minDate: minDate,
                    maxDate: maxDate,
                    disableDayFn: function(date) {
                        // Solo lunes
                        return date.getDay() !== 1;
                    },
                    toString(date, format) {
                        const day = ("0" + date.getDate()).slice(-2);
                        const month = ("0" + (date.getMonth() + 1)).slice(-2);
                        return date.getFullYear() + '-' + month + '-' + day;
                    },
                    onSelect: function(date) {
                        // Calcular viernes de esa semana
                        var viernes = new Date(date);
                        viernes.setDate(date.getDate() + 4);
                        // Validar rango
                        if (minDate && viernes < minDate) return;
                        if (maxDateRaw && viernes > maxDateRaw) return;
                        const day = ("0" + viernes.getDate()).slice(-2);
                        const month = ("0" + (viernes.getMonth() + 1)).slice(-2);
                        const viernesStr = viernes.getFullYear() + '-' + month + '-' + day;
                        semanaFinInput.value = viernesStr;
                    }
                });
            }
            if (semanaFinInput) {
                var minDateFin = semanaFinInput.dataset.min ? new Date(semanaFinInput.dataset.min) : null;
                var maxDateFin = semanaFinInput.dataset.max ? new Date(semanaFinInput.dataset.max) : null;
                var pickerSemanaFin = new Pikaday({
                    field: semanaFinInput,
                    format: 'YYYY-MM-DD',
                    minDate: minDateFin,
                    maxDate: maxDateFin,
                    disableDayFn: function(date) {
                        // Solo viernes
                        return date.getDay() !== 5;
                    },
                    toString(date, format) {
                        const day = ("0" + date.getDate()).slice(-2);
                        const month = ("0" + (date.getMonth() + 1)).slice(-2);
                        return date.getFullYear() + '-' + month + '-' + day;
                    }
                });
            }
            // --- FIN MODIFICACIÓN ---

            window.quill_actividades_previas = new Quill('#editor_actividades_previas', {
                theme: 'snow',
                placeholder: 'Describa las actividades previas a la clase...'
            });
            window.quill_contenido = new Quill('#editor_contenido', {
                theme: 'snow',
                placeholder: 'Describa el contenido de la semana...'
            });

            var dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
            window.quill_objetivo = {};
            window.quill_apertura = {};
            window.quill_desarrollo = {};
            window.quill_cierre = {};
            window.quill_trabajo = {};

            dias.forEach(function(dia) {
                window.quill_objetivo[dia] = new Quill('#editor_objetivo_' + dia, {
                    theme: 'snow',
                    placeholder: 'Objetivo...'
                });
                window.quill_apertura[dia] = new Quill('#editor_apertura_' + dia, {
                    theme: 'snow',
                    placeholder: 'Apertura...'
                });
                window.quill_desarrollo[dia] = new Quill('#editor_desarrollo_' + dia, {
                    theme: 'snow',
                    placeholder: 'Desarrollo...'
                });
                window.quill_cierre[dia] = new Quill('#editor_cierre_' + dia, {
                    theme: 'snow',
                    placeholder: 'Cierre...'
                });
                window.quill_trabajo[dia] = new Quill('#editor_trabajo_autonomo_' + dia, {
                    theme: 'snow',
                    placeholder: 'Trabajo autónomo...'
                });
            });

            // Reasigna el submit del formulario
            var formSemana = document.getElementById('formSemana');
            if (formSemana) {
                formSemana.addEventListener('submit', function(e) {
                    e.preventDefault();
                    document.getElementById('input_actividades_previas').value = window.quill_actividades_previas.root.innerHTML;
                    document.getElementById('input_contenido').value = window.quill_contenido.root.innerHTML;
                    dias.forEach(function(dia) {
                        document.getElementById('input_objetivo_' + dia).value = window.quill_objetivo[dia].root.innerHTML;
                        document.getElementById('input_apertura_' + dia).value = window.quill_apertura[dia].root.innerHTML;
                        document.getElementById('input_desarrollo_' + dia).value = window.quill_desarrollo[dia].root.innerHTML;
                        document.getElementById('input_cierre_' + dia).value = window.quill_cierre[dia].root.innerHTML;
                        document.getElementById('input_trabajo_autonomo_' + dia).value = window.quill_trabajo[dia].root.innerHTML;
                    });

                    var formData = new FormData(this);
                    fetch('/sysplanificacion/app/Semana/createSemana.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('semanaSuccess').style.display = 'block';
                                var btnPDF = document.getElementById('btnVisualizarPDF');
                                if (btnPDF) btnPDF.disabled = false;

                                // Guardar datos para el PDF
                                window.semanaId = data.semana_id;
                                window.semanaInicio = document.getElementById('semana_inicio').value;
                                window.semanaFin = document.getElementById('semana_fin').value;
                                // Obtener el nombre de la unidad desde el DOM
                                var unidadNombreDiv = document.querySelector('.alert-info');
                                if (unidadNombreDiv) {
                                    var strong = unidadNombreDiv.querySelector('strong');
                                    if (strong && strong.nextSibling) {
                                        window.unidadNombre = strong.nextSibling.textContent.trim();
                                    } else {
                                        window.unidadNombre = '';
                                    }
                                } else {
                                    window.unidadNombre = '';
                                }

                                // Asignar evento al botón PDF (solo una vez)
                                if (btnPDF && !btnPDF.dataset.pdfReady) {
                                    btnPDF.addEventListener('click', function() {
                                        if (!window.semanaId || !window.semanaInicio || !window.semanaFin || !window.unidadNombre) {
                                            alert('Faltan datos para generar el PDF.');
                                            return;
                                        }
                                        // --- MODIFICACIÓN: Cargar PDF en el mismo modal ---
                                        fetch(`/sysplanificacion/app/GestionPDF/generarPdf.php?semana_id=${encodeURIComponent(window.semanaId)}&unidad_nombre=${encodeURIComponent(window.unidadNombre)}&semana_inicio=${encodeURIComponent(window.semanaInicio)}&semana_fin=${encodeURIComponent(window.semanaFin)}`)
                                            .then(res => res.text())
                                            .then(html => {
                                                document.getElementById('modalUnidadBody').innerHTML = html;
                                            });
                                    });
                                    btnPDF.dataset.pdfReady = "1";
                                }
                            } else {
                                alert('Error al guardar la semana: ' + (data.message || ''));
                            }
                        })
                        .catch(err => {
                            alert('Error en la conexión o en el servidor.');
                        });
                });
            }
        }

        // --- INICIO: Inicializar Pikaday para cada campo de fecha de entrega (gestionarSemana) ---
        window.inicializarPikadayEntrega = function() {
            if (typeof Pikaday === 'undefined') return;
            document.querySelectorAll('.fecha-entrega').forEach(function(input) {
                if (!input._pikaday) { // Evita inicializar dos veces
                    input._pikaday = new Pikaday({
                        field: input,
                        format: 'YYYY-MM-DD',
                        minDate: input.dataset.min ? new Date(input.dataset.min) : null,
                        maxDate: input.dataset.max ? new Date(input.dataset.max) : null,
                        toString(date, format) {
                            const day = ("0" + date.getDate()).slice(-2);
                            const month = ("0" + (date.getMonth() + 1)).slice(-2);
                            return date.getFullYear() + '-' + month + '-' + day;
                        }
                    });
                }
            });
        };
        // --- FIN ---
    });
</script>
<script>
    let carruselIndex = 0;
    const tarjetasPorVista = 3; // Cambia este valor según el tamaño de tus tarjetas

    function cargarUnidades(asignatura_codigo) {
        if (!asignatura_codigo) {
            document.getElementById('carruselUnidadesContainer').style.display = 'none';
            document.getElementById('carruselUnidadesInner').innerHTML = '';
            return;
        }
        fetch('/sysplanificacion/app/Unidad/get_unidades.php?asignatura_codigo=' + encodeURIComponent(asignatura_codigo))
            .then(res => res.json())
            .then(unidades => {
                const container = document.getElementById('carruselUnidadesContainer');
                const inner = document.getElementById('carruselUnidadesInner');
                const btnIzq = document.getElementById('btnCarruselIzq');
                const btnDer = document.getElementById('btnCarruselDer');
                if (!unidades || unidades.length === 0) {
                    container.style.display = 'none';
                    inner.innerHTML = '';
                    btnIzq.style.display = 'none';
                    btnDer.style.display = 'none';
                    return;
                }
                container.style.display = 'block';
                inner.innerHTML = '';
                carruselIndex = 0;

                unidades.forEach(unidad => {
                    const card = document.createElement('div');
                    card.style.display = 'flex';
                    card.style.flexDirection = 'column';
                    card.style.alignItems = 'center';
                    card.style.justifyContent = 'center';
                    card.style.background = '#fff';
                    card.style.border = '1px solid #ddd';
                    card.style.borderRadius = '8px';
                    card.style.padding = '18px 28px';
                    card.style.minWidth = '160px';
                    card.style.maxWidth = '180px';
                    card.style.boxShadow = '0 2px 6px #0001';
                    card.style.textAlign = 'center';

                    const nombre = document.createElement('div');
                    nombre.style.fontWeight = 'bold';
                    nombre.style.marginBottom = '12px';
                    nombre.textContent = unidad.nombre;

                    const btn = document.createElement('button');
                    btn.className = 'btn btn-primary btn-sm';
                    btn.innerHTML = '<i class="bi bi-eye"></i> Ver/Editar';
                    btn.onclick = function() {
                        verUnidad(unidad.id_unidad);
                    };

                    card.appendChild(nombre);
                    card.appendChild(btn);
                    inner.appendChild(card);
                });

                actualizarCarrusel(unidades.length);

                btnIzq.onclick = function() {
                    if (carruselIndex > 0) {
                        carruselIndex--;
                        actualizarCarrusel(unidades.length);
                    }
                };
                btnDer.onclick = function() {
                    if (carruselIndex < unidades.length - tarjetasPorVista) {
                        carruselIndex++;
                        actualizarCarrusel(unidades.length);
                    }
                };
            });
    }

    function actualizarCarrusel(total) {
        const inner = document.getElementById('carruselUnidadesInner');
        const btnIzq = document.getElementById('btnCarruselIzq');
        const btnDer = document.getElementById('btnCarruselDer');
        const anchoTarjeta = 204; // minWidth + gap (160+24 aprox)
        inner.style.transform = `translateX(-${carruselIndex * anchoTarjeta}px)`;
        btnIzq.style.display = carruselIndex > 0 ? 'inline-block' : 'none';
        btnDer.style.display = (carruselIndex < total - tarjetasPorVista) ? 'inline-block' : 'none';
    }

    // Llama a cargarUnidades cuando cambie la asignatura
    document.getElementById('asignatura').addEventListener('change', function() {
        cargarUnidades(this.value);
    });
    // Llama a cargarUnidades cuando cambie la asignatura
    document.getElementById('asignatura').addEventListener('change', function() {
        cargarUnidades(this.value);
    });

    // Llama a cargarUnidades después de guardar una unidad (dentro del .then de tu fetch POST)
    function recargarCarruselDespuesDeGuardarUnidad(asignatura_codigo) {
        cargarUnidades(asignatura_codigo);
    }

    // Modal para ver/editar unidad
    function verUnidad(id_unidad) {
        fetch('/sysplanificacion/app/Unidad/get_unidades.php?id_unidad=' + id_unidad)
            .then(res => res.json())
            .then(unidad => {
                // ...dentro de la función verUnidad...
                document.getElementById('modalEditarUnidadContent').innerHTML = `
    <div class="modal-header">
        <h5 class="modal-title" id="modalEditarUnidadLabel">Editar Unidad</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
    </div>
    <div class="modal-body" id="modalUnidadBodyEditar">
        <form id="formEditarUnidad" autocomplete="off">
            <input type="hidden" name="id_unidad" value="${unidad.id_unidad}">
            <table class="table table-bordered" style="background: #fff;">
                <tr>
                    <td colspan="4" style="text-align:center; background:#eaeaea;">
                        <strong>Unidad N°</strong>
<input type="number" name="numero_unidad" min="1" style="width:60px; display:inline-block;" value="${unidad.numero_unidad !== null && unidad.numero_unidad !== undefined ? unidad.numero_unidad : ''}" required>                        &nbsp;&nbsp;<strong>Nombre:</strong>
                        <input type="text" name="nombre" style="width:40%;" value="${unidad.nombre || ''}" required>
                    </td>
                </tr>
                <tr>
                    <!-- Objetivo de la unidad -->
<td style="width:30%; vertical-align:top;">
    <strong>Objetivo de la unidad:</strong>
    <div id="editor_objetivo_editar" class="quill-editor"></div>
    <input type="hidden" name="objetivo_unidad" id="input_objetivo_unidad_editar">
    <br>
    <strong>Bibliografía:</strong>
    <div id="editor_bibliografia_editar" class="quill-editor"></div>
    <input type="hidden" name="bibliografia" id="input_bibliografia_editar">
</td>
                    <!-- Metodología -->
                    <td style="width:20%; vertical-align:top;">
                        <strong>Metodologías de evaluación de la unidad:</strong>
                        <div id="editor_metodologia_editar" class="quill-editor"></div>
                        <input type="hidden" name="metodologia" id="input_metodologia_editar">
                    </td>
                    <!-- Actividades de recuperación -->
                    <td style="width:20%; vertical-align:top;">
                        <strong>Actividades de recuperación de la unidad:</strong>
                        <div id="editor_actividades_editar" class="quill-editor"></div>
                        <input type="hidden" name="actividades_recuperacion" id="input_actividades_editar">
                    </td>
                    <!-- Recursos didácticos -->
                    <td style="width:30%; vertical-align:top;">
                        <strong>Equipo/Herramienta/Recursos didácticos de la unidad:</strong>
                        <div id="editor_recursos_editar" class="quill-editor"></div>
                        <input type="hidden" name="recursos_didacticos" id="input_recursos_editar">
                    </td>
                </tr>
            </table>
            <div class="text-end">
                <button type="submit" class="btn btn-success">Guardar Cambios</button>
                <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </form>
    </div>
`;
                // ...luego sigue igual la inicialización de Quill y el submit...
                var modal = new bootstrap.Modal(document.getElementById('modalEditarUnidad'));
                modal.show();

                // Inicializar Quill y cargar datos existentes
                // ...después de insertar el HTML...
                setTimeout(function() {
                    var quill_objetivo = new Quill('#editor_objetivo_editar', {
                        theme: 'snow',
                        placeholder: 'Escriba el objetivo de la unidad...'
                    });
                    var quill_bibliografia = new Quill('#editor_bibliografia_editar', {
                        theme: 'snow',
                        placeholder: 'Ingrese la bibliografía...'
                    });
                    var quill_metodologia = new Quill('#editor_metodologia_editar', {
                        theme: 'snow',
                        placeholder: 'Describa la metodología...'
                    });
                    var quill_actividades = new Quill('#editor_actividades_editar', {
                        theme: 'snow',
                        placeholder: 'Describa las actividades de recuperación...'
                    });
                    var quill_recursos = new Quill('#editor_recursos_editar', {
                        theme: 'snow',
                        placeholder: 'Describa los recursos didácticos...'
                    });

                    // Cargar datos existentes en Quill
                    quill_objetivo.root.innerHTML = unidad.objetivo_unidad || '';
                    quill_bibliografia.root.innerHTML = unidad.bibliografia || '';
                    quill_metodologia.root.innerHTML = unidad.metodologia || '';
                    quill_actividades.root.innerHTML = unidad.actividades_recuperacion || '';
                    quill_recursos.root.innerHTML = unidad.recursos_didacticos || '';

                    document.getElementById('formEditarUnidad').addEventListener('submit', function(e) {
                        e.preventDefault();
                        document.getElementById('input_objetivo_unidad_editar').value = quill_objetivo.root.innerHTML;
                        document.getElementById('input_bibliografia_editar').value = quill_bibliografia.root.innerHTML;
                        document.getElementById('input_metodologia_editar').value = quill_metodologia.root.innerHTML;
                        document.getElementById('input_actividades_editar').value = quill_actividades.root.innerHTML;
                        document.getElementById('input_recursos_editar').value = quill_recursos.root.innerHTML;

                        var formData = new FormData(this);
                        fetch('/sysplanificacion/app/Unidad/updateUnidad.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    modal.hide();
                                    cargarUnidades(document.getElementById('asignatura').value);
                                } else {
                                    alert('Error al actualizar: ' + (data.message || ''));
                                }
                            });
                    });
                }, 300);
            });
    }
    // BUSCADOR DE UNIDADES EN CARRUSEL
    let todasLasUnidades = [];
    let fuse = null;

    function cargarUnidades(asignatura_codigo) {
        if (!asignatura_codigo) {
            document.getElementById('carruselUnidadesContainer').style.display = 'none';
            document.getElementById('buscadorCarruselContainer').style.display = 'none';
            document.getElementById('carruselUnidadesInner').innerHTML = '';
            return;
        }
        fetch('/sysplanificacion/app/Unidad/get_unidades.php?asignatura_codigo=' + encodeURIComponent(asignatura_codigo))
            .then(res => res.json())
            .then(unidades => {
                todasLasUnidades = unidades || [];
                // Inicializa Fuse.js
                fuse = new Fuse(todasLasUnidades, {
                    keys: ['nombre'],
                    threshold: 0.4 // Ajusta la sensibilidad
                });
                mostrarUnidadesFiltradas('');
                document.getElementById('buscadorCarruselContainer').style.display = (todasLasUnidades.length > 0) ? 'block' : 'none';
            });
    }

    function mostrarUnidadesFiltradas(filtro) {
        let unidades = todasLasUnidades;
        if (filtro && fuse) {
            unidades = fuse.search(filtro).map(res => res.item);
        }
        const container = document.getElementById('carruselUnidadesContainer');
        const inner = document.getElementById('carruselUnidadesInner');
        const btnIzq = document.getElementById('btnCarruselIzq');
        const btnDer = document.getElementById('btnCarruselDer');
        if (!unidades || unidades.length === 0) {
            container.style.display = 'none';
            inner.innerHTML = '';
            btnIzq.style.display = 'none';
            btnDer.style.display = 'none';
            return;
        }
        container.style.display = 'block';
        inner.innerHTML = '';
        carruselIndex = 0;

        unidades.forEach(unidad => {
            const card = document.createElement('div');
            card.style.display = 'flex';
            card.style.flexDirection = 'column';
            card.style.alignItems = 'center';
            card.style.justifyContent = 'center';
            card.style.background = '#fff';
            card.style.border = '1px solid #ddd';
            card.style.borderRadius = '8px';
            card.style.padding = '18px 28px';
            card.style.minWidth = '160px';
            card.style.maxWidth = '180px';
            card.style.boxShadow = '0 2px 6px #0001';
            card.style.textAlign = 'center';

            const nombre = document.createElement('div');
            nombre.style.fontWeight = 'bold';
            nombre.style.marginBottom = '12px';
            nombre.textContent = unidad.nombre;

            const btn = document.createElement('button');
            btn.className = 'btn btn-primary btn-sm';
            btn.innerHTML = '<i class="bi bi-eye"></i> Ver/Editar';
            btn.onclick = function() {
                verUnidad(unidad.id_unidad);
            };

            card.appendChild(nombre);
            card.appendChild(btn);
            inner.appendChild(card);
        });

        actualizarCarrusel(unidades.length);

        btnIzq.onclick = function() {
            if (carruselIndex > 0) {
                carruselIndex--;
                actualizarCarrusel(unidades.length);
            }
        };
        btnDer.onclick = function() {
            if (carruselIndex < unidades.length - tarjetasPorVista) {
                carruselIndex++;
                actualizarCarrusel(unidades.length);
            }
        };
    }

    // Evento para el input de búsqueda
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscadorCarrusel');
        if (buscador) {
            buscador.addEventListener('input', function() {
                mostrarUnidadesFiltradas(this.value);
            });
        }
    });
</script>
</body>

</html>