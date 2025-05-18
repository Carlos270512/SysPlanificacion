<?php
$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$nombre_docente = '';
$fecha_actual = (new DateTime('now', new DateTimeZone('America/Guayaquil')))->format('Y-m-d H:i:s');

if ($codigo) {
    $pdo = require_once __DIR__ . '/../config/conexion.php';
    $stmt = $pdo->prepare("
        SELECT d.nombre AS nombre_docente
        FROM asignatura a
        INNER JOIN docente d ON a.docente_codigo = d.codigo
        WHERE a.codigo = ?
    ");
    $stmt->execute([$codigo]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $nombre_docente = $row['nombre_docente'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestionar Reportes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="assets/css/gestionarReportestyless.css">
    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .quill-editor {
            min-height: 90px;
            background: #fff;
            border-radius: .375rem;
            border: 1px solid #ced4da;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Encabezado</h2>
        <p><strong>Asignatura:</strong> <?= htmlspecialchars($nombre) ?> - <?= htmlspecialchars($codigo) ?></p>
        <p><strong>Docente:</strong> <?= htmlspecialchars($nombre_docente) ?></p>
        <p><strong>Fecha:</strong> <?= htmlspecialchars($fecha_actual) ?></p>
        <hr>
        <?php if ($codigo): ?>
            <?php
            $stmt_unidades = $pdo->prepare("SELECT id_unidad, nombre FROM unidad WHERE asignatura_codigo = ?");
            $stmt_unidades->execute([$codigo]);
            $unidades = $stmt_unidades->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <h3>Unidades de la Asignatura</h3>
            <div id="botones-unidades">
                <?php if ($unidades): ?>
                    <?php foreach ($unidades as $unidad): ?>
                        <button
                            class="btn btn-primary m-1 btn-unidad"
                            data-id="<?= htmlspecialchars($unidad['id_unidad']) ?>">
                            <?= htmlspecialchars($unidad['nombre']) ?>
                        </button>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay unidades registradas para esta asignatura.</p>
                <?php endif; ?>
            </div>
            <hr>
            <div id="unidad-detalle"></div>
        <?php endif; ?>
    </div>

    <!-- Modal Agregar Semana (contenido dinámico) -->
    <div class="modal fade" id="modalAgregarSemana" tabindex="-1" aria-labelledby="modalAgregarSemanaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="modalSemanaContent">
                <!-- Aquí se cargará el formulario dinámicamente -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        let idUnidadActual = null;

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-unidad').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const idUnidad = this.getAttribute('data-id');
                    idUnidadActual = idUnidad;
                    fetch('verUnidad.php?id_unidad=' + encodeURIComponent(idUnidad))
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.id_unidad) {
                                document.getElementById('unidad-detalle').innerHTML = `
                                <form class="border p-3 mt-3 bg-light">
                                    <div class="mb-2">
                                        <label class="form-label">Nombre de la Unidad</label>
                                        <input type="text" class="form-control" value="${data.nombre || ''}" readonly>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Objetivo</label>
                                        <div class="campo-html">${data.objetivo_unidad || ''}</div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Metodología</label>
                                        <div class="campo-html">${data.metodologia || ''}</div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Actividades de Recuperación</label>
                                        <div class="campo-html">${data.actividades_recuperacion || ''}</div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Recursos Didácticos</label>
                                        <div class="campo-html">${data.recursos_didacticos || ''}</div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Semana Inicio</label>
                                        <input type="date" class="form-control" value="${data.semana_inicio || ''}" readonly>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Semana Fin</label>
                                        <input type="date" class="form-control" value="${data.semana_fin || ''}" readonly>
                                    </div>
                                    <div class="mt-3 d-flex gap-2">
                                        <button type="button" class="btn btn-success" onclick="abrirModalSemana(${data.id_unidad})">Agregar nueva semana</button>
                                        <a href="verSemanas.php?id_unidad=${data.id_unidad}" class="btn btn-info">Ver semanas</a>
                                    </div>
                                </form>
                            `;
                            } else {
                                document.getElementById('unidad-detalle').innerHTML = '<div class="alert alert-warning mt-3">No se encontraron datos de la unidad.</div>';
                            }
                        })
                        .catch(() => {
                            document.getElementById('unidad-detalle').innerHTML = '<div class="alert alert-danger mt-3">Error al cargar los datos de la unidad.</div>';
                        });
                });
            });
        });

        // Función global para abrir el modal y cargar el formulario dinámicamente
        function abrirModalSemana(idUnidad) {
        fetch('nuevaSemana.php?id_unidad=' + encodeURIComponent(idUnidad))
            .then(res => res.text())
            .then(html => {
                document.getElementById('modalSemanaContent').innerHTML = `
                <div class="modal-header">
                  <h5 class="modal-title" id="modalAgregarSemanaLabel">Agregar Nueva Semana</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  ${html}
                </div>
            `;
                var modal = new bootstrap.Modal(document.getElementById('modalAgregarSemana'));
                modal.show();

                // Inicializar Flatpickr en el campo cargado dinámicamente
                flatpickr("#fecha_semana", {
                    dateFormat: "Y-m-d",
                    "locale": "es",
                    disable: [
                        function(date) {
                            return date.getDay() !== 1; // Solo lunes
                        }
                    ],
                    onChange: function(selectedDates, dateStr, instance) {
                        if (window.setFechasSemana) window.setFechasSemana(dateStr);
                    }
                });

                // Automatiza las fechas de los días y labels
                (function() {
                    const fechaSemanaInput = document.getElementById('fecha_semana');
                    const dias = [
                        { key: 'lunes', nombre: 'Lunes' },
                        { key: 'martes', nombre: 'Martes' },
                        { key: 'miercoles', nombre: 'Miércoles' },
                        { key: 'jueves', nombre: 'Jueves' },
                        { key: 'viernes', nombre: 'Viernes' }
                    ];

                    function setFechasSemana(fechaLunesStr) {
                        if (!fechaLunesStr) {
                            dias.forEach((dia) => {
                                const fechaInput = document.getElementById('fecha_' + dia.key);
                                const entregaInput = document.getElementById('fecha_entrega_' + dia.key);
                                const label = document.getElementById('label_' + dia.key);
                                if (fechaInput) fechaInput.value = '';
                                if (entregaInput) entregaInput.value = '';
                                if (label) label.textContent = dia.nombre;
                            });
                            const viernesDiv = document.getElementById('fecha_viernes');
                            if (viernesDiv) viernesDiv.textContent = '';
                            return;
                        }
                        const fechaLunes = new Date(fechaLunesStr + 'T00:00:00');
                        dias.forEach((dia, i) => {
                            const fechaDia = new Date(fechaLunes);
                            fechaDia.setDate(fechaLunes.getDate() + i);
                            const yyyy = fechaDia.getFullYear();
                            const mm = String(fechaDia.getMonth() + 1).padStart(2, '0');
                            const dd = String(fechaDia.getDate()).padStart(2, '0');
                            const fechaStr = `${yyyy}-${mm}-${dd}`;
                            const fechaInput = document.getElementById('fecha_' + dia.key);
                            const entregaInput = document.getElementById('fecha_entrega_' + dia.key);
                            const label = document.getElementById('label_' + dia.key);
                            if (fechaInput) fechaInput.value = fechaStr;
                            if (entregaInput) entregaInput.value = fechaStr;
                            if (label) label.textContent = `${dia.nombre} - ${fechaStr}`;
                            if (dia.key === 'viernes') {
                                const viernesDiv = document.getElementById('fecha_viernes');
                                if (viernesDiv) viernesDiv.textContent = 'Viernes de esa semana: ' + fechaStr;
                            }
                        });
                    }
                    window.setFechasSemana = setFechasSemana;

                    fechaSemanaInput.addEventListener('change', function() {
                        setFechasSemana(this.value);
                    });

                    if (fechaSemanaInput.value) {
                        setFechasSemana(fechaSemanaInput.value);
                    }
                })();

                // Inicializar Quill en todos los campos de la semana (excepto fecha)
                const quillFields = [
                    'actividades_previas', 'contenido',
                    // Lunes
                    'objetivo_lunes', 'apertura_lunes', 'desarrollo_lunes', 'cierre_lunes', 'trabajo_autonomo_lunes',
                    'tiempo_objetivo_lunes', 'tiempo_apertura_lunes', 'tiempo_desarrollo_lunes', 'tiempo_cierre_lunes',
                    // Martes
                    'objetivo_martes', 'apertura_martes', 'desarrollo_martes', 'cierre_martes', 'trabajo_autonomo_martes',
                    'tiempo_objetivo_martes', 'tiempo_apertura_martes', 'tiempo_desarrollo_martes', 'tiempo_cierre_martes',
                    // Miércoles
                    'objetivo_miercoles', 'apertura_miercoles', 'desarrollo_miercoles', 'cierre_miercoles', 'trabajo_autonomo_miercoles',
                    'tiempo_objetivo_miercoles', 'tiempo_apertura_miercoles', 'tiempo_desarrollo_miercoles', 'tiempo_cierre_miercoles',
                    // Jueves
                    'objetivo_jueves', 'apertura_jueves', 'desarrollo_jueves', 'cierre_jueves', 'trabajo_autonomo_jueves',
                    'tiempo_objetivo_jueves', 'tiempo_apertura_jueves', 'tiempo_desarrollo_jueves', 'tiempo_cierre_jueves',
                    // Viernes
                    'objetivo_viernes', 'apertura_viernes', 'desarrollo_viernes', 'cierre_viernes', 'trabajo_autonomo_viernes',
                    'tiempo_objetivo_viernes', 'tiempo_apertura_viernes', 'tiempo_desarrollo_viernes', 'tiempo_cierre_viernes'
                ];
                window.quillSemana = {};
                quillFields.forEach(function(field) {
                    const editorDiv = document.getElementById('quill_' + field);
                    if (editorDiv) {
                        window.quillSemana[field] = new Quill(editorDiv, { theme: 'snow' });
                    }
                });

                // Antes de enviar el formulario, copiar el contenido de Quill a los inputs ocultos
                document.getElementById('formAgregarSemana').addEventListener('submit', function(e) {
                    quillFields.forEach(function(field) {
                        if (window.quillSemana[field]) {
                            document.getElementById('input_' + field).value = window.quillSemana[field].root.innerHTML;
                        }
                    });
                });

                // Manejar el submit del formulario cargado (AJAX)
                document.getElementById('formAgregarSemana').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const form = e.target;
                    const formData = new FormData(form);
                    fetch('agregarSemana.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                var modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarSemana'));
                                modal.hide();
                                setTimeout(function() {
                                    var modalExito = new bootstrap.Modal(document.getElementById('modalExitoSemana'));
                                    modalExito.show();
                                }, 400);
                            } else {
                                alert('Error al agregar la semana.');
                            }
                        })
                        .catch(() => alert('Error al agregar la semana.'));
                });
            });
    }

</script>

<!-- Modal de éxito -->
<div class="modal fade" id="modalExitoSemana" tabindex="-1" aria-labelledby="modalExitoSemanaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><!-- Se agregó modal-dialog-centered -->
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalExitoSemanaLabel">¡Éxito!</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        La semana se agregó correctamente.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>