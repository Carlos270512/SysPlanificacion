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
    <style>
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: #333;
            border-radius: 50%;
        }
    </style>
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
                    // Limpiar carrusel
                    document.getElementById('carruselUnidadesContainer').style.display = 'none';
                    document.getElementById('carruselUnidadesInner').innerHTML = '';
                });
        }

        function mostrarDatosAsignatura(valor) {
            if (!valor) {
                document.getElementById('info-asignatura').innerHTML = '';
                mostrarCamposUnidad(false);
                asignaturaSeleccionadaCodigo = '';
                asignaturaSeleccionadaNombre = '';
                document.getElementById('carruselUnidadesContainer').style.display = 'none';
                document.getElementById('carruselUnidadesInner').innerHTML = '';
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
            cargarUnidades(asignaturaSeleccionadaCodigo);
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

        // --- Carrusel de Unidades ---
        function cargarUnidades(asignatura_codigo) {
            if (!asignatura_codigo) {
                document.getElementById('carruselUnidadesContainer').style.display = 'none';
                document.getElementById('carruselUnidadesInner').innerHTML = '';
                return;
            }
            fetch(`/SysPlanificacion/app/Unidad/get_unidades.php?asignatura_codigo=${encodeURIComponent(asignatura_codigo)}`)
                .then(res => res.json())
                .then(unidades => {
                    const container = document.getElementById('carruselUnidadesContainer');
                    const inner = document.getElementById('carruselUnidadesInner');
                    if (!unidades || unidades.length === 0) {
                        container.style.display = 'none';
                        inner.innerHTML = '';
                        return;
                    }
                    container.style.display = 'block';
                    inner.innerHTML = '';
                    unidades.forEach((unidad, idx) => {
                        const active = idx === 0 ? 'active' : '';
                        inner.innerHTML += `
                            <div class="carousel-item ${active}">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h6 class="card-title">${unidad.nombre}</h6>
                                        <button class="btn btn-primary btn-sm" onclick="verUnidad(${unidad.id_unidad})">
                                            <i class="fa fa-eye"></i> Ver/Editar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                });
        }

        function verUnidad(id_unidad) {
            fetch(`/SysPlanificacion/app/Unidad/get_unidades.php?id_unidad=${id_unidad}`)
                .then(res => res.json())
                .then(unidad => {
                    document.getElementById('modalEditarUnidadContent').innerHTML = `
                        <form id="formEditarUnidad">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Editar Unidad</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="id_unidad" value="${unidad.id_unidad}">
                                <div class="mb-3">
                                    <label>Nombre Unidad</label>
                                    <input type="text" name="nombre" class="form-control" value="${unidad.nombre || ''}" required>
                                </div>
                                <div class="mb-3">
                                    <label>Objetivo Unidad</label>
                                    <div id="quill_objetivo_editar"></div>
                                    <input type="hidden" name="objetivo_unidad" id="objetivo_unidad_editar">
                                </div>
                                <div class="mb-3">
                                    <label>Metodología</label>
                                    <div id="quill_metodologia_editar"></div>
                                    <input type="hidden" name="metodologia" id="metodologia_editar">
                                </div>
                                <div class="mb-3">
                                    <label>Actividades de Recuperación</label>
                                    <div id="quill_actividades_editar"></div>
                                    <input type="hidden" name="actividades_recuperacion" id="actividades_editar">
                                </div>
                                <div class="mb-3">
                                    <label>Recursos Didácticos</label>
                                    <div id="quill_recursos_editar"></div>
                                    <input type="hidden" name="recursos_didacticos" id="recursos_editar">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Semana Inicio</label>
                                        <input type="date" name="semana_inicio" class="form-control" value="${unidad.semana_inicio || ''}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Semana Fin</label>
                                        <input type="date" name="semana_fin" class="form-control" value="${unidad.semana_fin || ''}">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Guardar Cambios</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </form>
                    `;
                    var modal = new bootstrap.Modal(document.getElementById('modalEditarUnidad'));
                    modal.show();

                    // Inicializar Quill y setear contenido
                    var quill_objetivo = new Quill('#quill_objetivo_editar', {
                        theme: 'snow'
                    });
                    var quill_metodologia = new Quill('#quill_metodologia_editar', {
                        theme: 'snow'
                    });
                    var quill_actividades = new Quill('#quill_actividades_editar', {
                        theme: 'snow'
                    });
                    var quill_recursos = new Quill('#quill_recursos_editar', {
                        theme: 'snow'
                    });
                    quill_objetivo.root.innerHTML = unidad.objetivo_unidad || '';
                    quill_metodologia.root.innerHTML = unidad.metodologia || '';
                    quill_actividades.root.innerHTML = unidad.actividades_recuperacion || '';
                    quill_recursos.root.innerHTML = unidad.recursos_didacticos || '';

                    document.getElementById('formEditarUnidad').addEventListener('submit', function(e) {
                        e.preventDefault();
                        document.getElementById('objetivo_unidad_editar').value = quill_objetivo.root.innerHTML;
                        document.getElementById('metodologia_editar').value = quill_metodologia.root.innerHTML;
                        document.getElementById('actividades_editar').value = quill_actividades.root.innerHTML;
                        document.getElementById('recursos_editar').value = quill_recursos.root.innerHTML;
                        var formData = new FormData(this);
                        fetch('/SysPlanificacion/app/Unidad/updateUnidad.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    modal.hide();
                                    cargarUnidades(asignaturaSeleccionadaCodigo);
                                } else {
                                    alert('Error al actualizar: ' + (data.message || ''));
                                }
                            });
                    });
                });
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
                    <!-- ...campos del formulario y unidad aquí... -->
                    <div id="info-asignatura"></div>
                    <div id="campos-unidad" style="display:none;">
                        <!-- ...campos de la unidad... -->
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Guardar Unidad y/o Generar PDF
                        </button>
                    </div>
                </form>
                <!-- Carrusel de Unidades fuera del form -->
                <div id="carruselUnidadesContainer" class="mb-4" style="display:none;">
                    <h5>Unidades Generadas</h5>
                    <div id="carruselUnidades" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner" id="carruselUnidadesInner"></div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carruselUnidades" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carruselUnidades" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
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
        </div>
    </div>
    <!-- ...resto del código, modales, scripts... -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>