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
        #unidadesCarousel {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 8px;
        }
        #unidadesCarousel .btn {
            min-width: 160px;
            margin-right: 8px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const asignaturas = <?php echo json_encode($asignaturas); ?>;
            const carrera = <?php echo json_encode($docente ? $docente['carrera'] : ''); ?>;
            const select = document.getElementById('asignatura');
            const card = document.getElementById('asignaturaCard');
            const cardBody = document.getElementById('asignaturaCardBody');
            const unidadesSection = document.getElementById('unidadesSection');
            const unidadesCarousel = document.getElementById('unidadesCarousel');
            const unidadCard = document.getElementById('unidadCard');
            const unidadCardBody = document.getElementById('unidadCardBody');

            function getModalidad(jornada) {
                switch (jornada) {
                    case 'PM': return 'Presencial';
                    case 'PN': return 'Presencial Nocturna';
                    case 'S': return 'Semi presencial';
                    case 'HM': return 'Híbrida';
                    case 'V': return 'Virtual';
                    default: return 'Desconocida';
                }
            }

            function cargarUnidades(asignatura_codigo) {
                unidadesCarousel.innerHTML = '';
                unidadesSection.style.display = 'none';
                unidadCard.style.display = 'none';
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
                                btn.onclick = function () {
                                    mostrarUnidad(u.id_unidad);
                                };
                                unidadesCarousel.appendChild(btn);
                            });
                        }
                    });
            }

            function mostrarUnidad(id_unidad) {
                fetch('../app/get_unidades.php?id_unidad=' + encodeURIComponent(id_unidad))
                    .then(res => res.json())
                    .then(unidad => {
                        if (unidad && unidad.id_unidad) {
                            unidadCard.style.display = 'block';
                            unidadCardBody.innerHTML = `
                                <h5 class="card-title mb-3">${unidad.nombre}</h5>
                                <div class="table-responsive">
                                <table class="asig-table">
                                    <tr>
                                        <td><strong>Objetivo:</strong> ${unidad.objetivo_unidad || ''}</td>
                                        <td><strong>Metodología:</strong> ${unidad.metodologia || ''}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Actividades Recuperación:</strong> ${unidad.actividades_recuperacion || ''}</td>
                                        <td><strong>Recursos Didácticos:</strong> ${unidad.recursos_didacticos || ''}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Semana Inicio:</strong> ${unidad.semana_inicio ? (new Date(unidad.semana_inicio)).toLocaleDateString() : ''}</td>
                                        <td><strong>Semana Fin:</strong> ${unidad.semana_fin ? (new Date(unidad.semana_fin)).toLocaleDateString() : ''}</td>
                                    </tr>
                                </table>
                                </div>
                            `;
                        } else {
                            unidadCard.style.display = 'none';
                        }
                    });
            }

            select.addEventListener('change', function () {
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
        <?php else: ?>
            <div class="alert alert-warning">No tienes asignaturas asignadas.</div>
        <?php endif; ?>
    </div>
</body>
</html>