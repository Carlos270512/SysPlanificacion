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
    <style>
        .asig-table td {
            padding: 0.5rem 1.2rem;
            vertical-align: middle;
            border: none;
        }

        .asig-table tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .asig-table strong {
            min-width: 110px;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .asig-table td {
                display: block;
                width: 100%;
                border-bottom: none;
            }

            .asig-table tr {
                display: block;
                margin-bottom: 1rem;
            }
        }

        #unidadesCarousel,
        #semanasCarousel {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 8px;
        }

        #unidadesCarousel .btn,
        #semanasCarousel .btn {
            min-width: 160px;
            margin-right: 8px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const asignaturas = <?php echo json_encode($asignaturas); ?>;
            const carrera = <?php echo json_encode($docente ? $docente['carrera'] : ''); ?>;
            const select = document.getElementById('asignatura');
            const card = document.getElementById('asignaturaCard');
            const cardBody = document.getElementById('asignaturaCardBody');
            const unidadesSection = document.getElementById('unidadesSection');
            const unidadesCarousel = document.getElementById('unidadesCarousel');
            const unidadCard = document.getElementById('unidadCard');
            const unidadCardBody = document.getElementById('unidadCardBody');
            const semanasSection = document.getElementById('semanasSection');
            const semanasCarousel = document.getElementById('semanasCarousel');
            // const semanaCard = document.getElementById('semanaCard');
            // const semanaCardBody = document.getElementById('semanaCardBody');

            // Offcanvas
            const semanaOffcanvas = document.getElementById('semanaOffcanvas');
            const semanaOffcanvasBody = document.getElementById('semanaOffcanvasBody');
            let offcanvasInstance = null;

            function getModalidad(jornada) {
                switch (jornada) {
                    case 'PM':
                        return 'Presencial';
                    case 'PN':
                        return 'Presencial Nocturna';
                    case 'S':
                        return 'Semi presencial';
                    case 'HM':
                        return 'Híbrida';
                    case 'V':
                        return 'Virtual';
                    default:
                        return 'Desconocida';
                }
            }

            function cargarUnidades(asignatura_codigo) {
                unidadesCarousel.innerHTML = '';
                unidadesSection.style.display = 'none';
                unidadCard.style.display = 'none';
                semanasSection.style.display = 'none';
                // semanaCard.style.display = 'none';
                fetch('../app/get_unidades.php?asignatura_codigo=' + encodeURIComponent(asignatura_codigo))
                    .then(res => res.json())
                    .then(unidades => {
                        if (unidades.length > 0) {
                            unidadesSection.style.display = 'block';
                            unidades.forEach(u => {
                                const btn = document.createElement('button');
                                btn.className = 'btn btn-outline-primary m-1';
                                btn.textContent = u.nombre;
                                btn.setAttribute('data-id', u.id_unidad);
                                btn.onclick = function() {
                                    mostrarUnidad(u.id_unidad);
                                };
                                unidadesCarousel.appendChild(btn);
                            });
                        }
                    });
            }

            function cargarSemanas(id_unidad) {
                semanasCarousel.innerHTML = '';
                semanasSection.style.display = 'none';
                // semanaCard.style.display = 'none';
                fetch('../app/get_semanas.php?id_unidad=' + encodeURIComponent(id_unidad))
                    .then(res => res.json())
                    .then(semanas => {
                        if (semanas.length > 0) {
                            semanasSection.style.display = 'block';
                            semanas.forEach(s => {
                                const btn = document.createElement('button');
                                btn.className = 'btn btn-outline-success m-1';
                                let fechaLunes = s.fecha_lunes ? (new Date(s.fecha_lunes)).toLocaleDateString() : '';
                                let fechaViernes = s.fecha_viernes ? (new Date(s.fecha_viernes)).toLocaleDateString() : '';
                                btn.textContent = `Semana (${fechaLunes} - ${fechaViernes})`;
                                btn.setAttribute('data-id', s.id_semana);
                                btn.onclick = function() {
                                    mostrarSemana(s.id_semana);
                                };
                                semanasCarousel.appendChild(btn);
                            });
                        }
                    });
            }

            function mostrarUnidad(id_unidad) {
                // Convierte HTML de Quill a texto plano numerado y conserva saltos de línea
                function quillHtmlToText(html) {
                    if (!html) return '';
                    // Maneja listas ordenadas <ol><li>...</li></ol>
                    html = html.replace(/<ol[^>]*>([\s\S]*?)<\/ol>/gi, function(_, list) {
                        let i = 1;
                        return list.replace(/<li[^>]*>([\s\S]*?)<\/li>/gi, function(_, item) {
                            item = item.replace(/<[^>]+>/g, '').replace(/&nbsp;/g, ' ').trim();
                            return (i++) + '. ' + item + '\n';
                        });
                    });
                    // Maneja listas no ordenadas <ul><li>...</li></ul>
                    html = html.replace(/<ul[^>]*>([\s\S]*?)<\/ul>/gi, function(_, list) {
                        return list.replace(/<li[^>]*>([\s\S]*?)<\/li>/gi, function(_, item) {
                            item = item.replace(/<[^>]+>/g, '').replace(/&nbsp;/g, ' ').trim();
                            return '- ' + item + '\n';
                        });
                    });
                    // Reemplaza <br> y <p>
                    html = html.replace(/<br\s*\/?>/gi, '\n');
                    html = html.replace(/<p[^>]*>([\s\S]*?)<\/p>/gi, function(_, p) {
                        return p.replace(/<[^>]+>/g, '').replace(/&nbsp;/g, ' ').trim() + '\n';
                    });
                    // Quita cualquier etiqueta HTML restante
                    html = html.replace(/<[^>]+>/g, '');
                    // Quita saltos de línea extra
                    return html.replace(/\n{2,}/g, '\n').trim();
                }

                fetch('../app/get_unidades.php?id_unidad=' + encodeURIComponent(id_unidad))
                    .then(res => res.json())
                    .then(unidad => {
                        if (unidad && unidad.id_unidad) {
                            unidadCard.style.display = 'block';
                            // Convertir saltos de línea en <br> para mostrar cada punto en una línea
                            const recursosDidacticos = quillHtmlToText(unidad.recursos_didacticos).replace(/\n/g, '<br>');
                            unidadCardBody.innerHTML = `
                                <h5 class="card-title mb-3">${unidad.nombre}</h5>
                                <div class="table-responsive">
                                <table class="asig-table">
                                    <tr>
                                        <td>
                                            <strong>Objetivo:</strong>
                                            <input type="text" class="form-control" readonly value="${quillHtmlToText(unidad.objetivo_unidad)}">
                                            <br>
                                            <strong>Recursos Didácticos:</strong>
                                            <div class="form-control bg-white" style="height:auto;min-height:48px;overflow:auto;" readonly>
                                                ${recursosDidacticos}
                                            </div>
                                        </td>
                                        <td>
                                            <strong>Metodología:</strong>
                                            <input type="text" class="form-control" readonly value="${quillHtmlToText(unidad.metodologia)}">
                                        </td>
                                        <td>
                                            <strong>Actividades Recuperación:</strong>
                                            <input type="text" class="form-control" readonly value="${quillHtmlToText(unidad.actividades_recuperacion)}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Semana Inicio:</strong> ${unidad.semana_inicio ? (new Date(unidad.semana_inicio)).toLocaleDateString() : ''}</td>
                                        <td><strong>Semana Fin:</strong> ${unidad.semana_fin ? (new Date(unidad.semana_fin)).toLocaleDateString() : ''}</td>
                                    </tr>
                                </table>
                                </div>
                            `;
                            cargarSemanas(unidad.id_unidad);
                        } else {
                            unidadCard.style.display = 'none';
                            semanasSection.style.display = 'none';
                            // semanaCard.style.display = 'none';
                        }
                    });
            }

            function mostrarSemana(id_semana) {
                fetch('../app/get_semanas.php?id_semana=' + encodeURIComponent(id_semana))
                    .then(res => res.json())
                    .then(semana => {
                        if (semana && semana.id_semana) {
                            // Accordion HTML para los días de la semana
                            const dias = [{
                                    nombre: 'Lunes',
                                    fecha: semana.fecha_lunes,
                                    objetivo: semana.objetivo_lunes,
                                    tiempo_objetivo: semana.tiempo_objetivo_lunes,
                                    apertura: semana.apertura_lunes,
                                    tiempo_apertura: semana.tiempo_apertura_lunes,
                                    desarrollo: semana.desarrollo_lunes,
                                    tiempo_desarrollo: semana.tiempo_desarrollo_lunes,
                                    cierre: semana.cierre_lunes,
                                    tiempo_cierre: semana.tiempo_cierre_lunes,
                                    trabajo_autonomo: semana.trabajo_autonomo_lunes,
                                    fecha_entrega: semana.fecha_entrega_lunes
                                },
                                {
                                    nombre: 'Martes',
                                    fecha: semana.fecha_martes,
                                    objetivo: semana.objetivo_martes,
                                    tiempo_objetivo: semana.tiempo_objetivo_martes,
                                    apertura: semana.apertura_martes,
                                    tiempo_apertura: semana.tiempo_apertura_martes,
                                    desarrollo: semana.desarrollo_martes,
                                    tiempo_desarrollo: semana.tiempo_desarrollo_martes,
                                    cierre: semana.cierre_martes,
                                    tiempo_cierre: semana.tiempo_cierre_martes,
                                    trabajo_autonomo: semana.trabajo_autonomo_martes,
                                    fecha_entrega: semana.fecha_entrega_martes
                                },
                                {
                                    nombre: 'Miércoles',
                                    fecha: semana.fecha_miercoles,
                                    objetivo: semana.objetivo_miercoles,
                                    tiempo_objetivo: semana.tiempo_objetivo_miercoles,
                                    apertura: semana.apertura_miercoles,
                                    tiempo_apertura: semana.tiempo_apertura_miercoles,
                                    desarrollo: semana.desarrollo_miercoles,
                                    tiempo_desarrollo: semana.tiempo_desarrollo_miercoles,
                                    cierre: semana.cierre_miercoles,
                                    tiempo_cierre: semana.tiempo_cierre_miercoles,
                                    trabajo_autonomo: semana.trabajo_autonomo_miercoles,
                                    fecha_entrega: semana.fecha_entrega_miercoles
                                },
                                {
                                    nombre: 'Jueves',
                                    fecha: semana.fecha_jueves,
                                    objetivo: semana.objetivo_jueves,
                                    tiempo_objetivo: semana.tiempo_objetivo_jueves,
                                    apertura: semana.apertura_jueves,
                                    tiempo_apertura: semana.tiempo_apertura_jueves,
                                    desarrollo: semana.desarrollo_jueves,
                                    tiempo_desarrollo: semana.tiempo_desarrollo_jueves,
                                    cierre: semana.cierre_jueves,
                                    tiempo_cierre: semana.tiempo_cierre_jueves,
                                    trabajo_autonomo: semana.trabajo_autonomo_jueves,
                                    fecha_entrega: semana.fecha_entrega_jueves
                                },
                                {
                                    nombre: 'Viernes',
                                    fecha: semana.fecha_viernes,
                                    objetivo: semana.objetivo_viernes,
                                    tiempo_objetivo: semana.tiempo_objetivo_viernes,
                                    apertura: semana.apertura_viernes,
                                    tiempo_apertura: semana.tiempo_apertura_viernes,
                                    desarrollo: semana.desarrollo_viernes,
                                    tiempo_desarrollo: semana.tiempo_desarrollo_viernes,
                                    cierre: semana.cierre_viernes,
                                    tiempo_cierre: semana.tiempo_cierre_viernes,
                                    trabajo_autonomo: semana.trabajo_autonomo_viernes,
                                    fecha_entrega: semana.fecha_entrega_viernes
                                }
                            ];

                            let accordion = `
                <div class="mb-3">
                    <strong>Actividades Previas:</strong> ${semana.actividades_previas || ''}<br>
                    <strong>Tiempo Actividades Previas:</strong> ${semana.tiempo_actividades_previas || ''}<br>
                    <strong>Contenido:</strong> ${semana.contenido || ''}
                </div>
                <div class="accordion" id="accordionDiasSemana">
                `;

                            dias.forEach((dia, idx) => {
                                accordion += `
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading${dia.nombre}">
                            <button class="accordion-button ${idx !== 0 ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${dia.nombre}" aria-expanded="${idx === 0 ? 'true' : 'false'}" aria-controls="collapse${dia.nombre}">
                                ${dia.nombre} ${dia.fecha ? '(' + (new Date(dia.fecha)).toLocaleDateString() + ')' : ''}
                            </button>
                        </h2>
                        <div id="collapse${dia.nombre}" class="accordion-collapse collapse ${idx === 0 ? 'show' : ''}" aria-labelledby="heading${dia.nombre}" data-bs-parent="#accordionDiasSemana">
                            <div class="accordion-body">
                                <strong>Objetivo:</strong> ${dia.objetivo || ''}<br>
                                <strong>Tiempo Objetivo:</strong> ${dia.tiempo_objetivo || ''}<br>
                                <strong>Apertura:</strong> ${dia.apertura || ''}<br>
                                <strong>Tiempo Apertura:</strong> ${dia.tiempo_apertura || ''}<br>
                                <strong>Desarrollo:</strong> ${dia.desarrollo || ''}<br>
                                <strong>Tiempo Desarrollo:</strong> ${dia.tiempo_desarrollo || ''}<br>
                                <strong>Cierre:</strong> ${dia.cierre || ''}<br>
                                <strong>Tiempo Cierre:</strong> ${dia.tiempo_cierre || ''}<br>
                                <strong>Trabajo Autónomo:</strong> ${dia.trabajo_autonomo || ''}<br>
                                <strong>Fecha Entrega:</strong> ${dia.fecha_entrega ? (new Date(dia.fecha_entrega)).toLocaleDateString() : ''}
                            </div>
                        </div>
                    </div>
                    `;
                            });

                            accordion += `</div>`;

                            semanaOffcanvasBody.innerHTML = `
                    <h5 class="mb-3">Semana del ${semana.fecha_lunes ? (new Date(semana.fecha_lunes)).toLocaleDateString() : ''} al ${semana.fecha_viernes ? (new Date(semana.fecha_viernes)).toLocaleDateString() : ''}</h5>
                    ${accordion}
                `;
                            if (!offcanvasInstance) {
                                offcanvasInstance = new bootstrap.Offcanvas(semanaOffcanvas);
                            }
                            offcanvasInstance.show();
                        }
                    });
            }

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
                                <td><strong>Modalidad:</strong> ${getModalidad(asig.jornada)}</td>
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
                    cargarUnidades(asig.codigo);
                } else {
                    card.style.display = 'none';
                    unidadesSection.style.display = 'none';
                    unidadCard.style.display = 'none';
                    semanasSection.style.display = 'none';
                    // semanaCard.style.display = 'none';
                }
            });

            // Mostrar la card y unidades de la primera asignatura por defecto si existe
            if (select.value) {
                select.dispatchEvent(new Event('change'));
            }
        });
    </script>
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
            <!-- Carrusel de unidades -->
            <div id="unidadesSection" style="display:none;">
                <h5>Unidades de la asignatura</h5>
                <div id="unidadesCarousel" class="btn-group" role="group"></div>
            </div>
            <!-- Card de la unidad seleccionada -->
            <div id="unidadCard" class="card mb-4" style="display:none;">
                <div class="card-body" id="unidadCardBody"></div>
            </div>
            <!-- Carrusel de semanas -->
            <div id="semanasSection" style="display:none;">
                <h5>Semanas de la unidad</h5>
                <div id="semanasCarousel" class="btn-group" role="group"></div>
            </div>
            <!-- Offcanvas para mostrar detalles de la semana -->
            <div class="offcanvas offcanvas-end" tabindex="-1" id="semanaOffcanvas" aria-labelledby="semanaOffcanvasLabel"  style="--bs-offcanvas-width: 700px;">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="semanaOffcanvasLabel">Detalle de la Semana</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                </div>
                <div class="offcanvas-body" id="semanaOffcanvasBody">
                    <!-- Aquí se cargan los detalles de la semana -->
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No tienes asignaturas asignadas.</div>
        <?php endif; ?>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>