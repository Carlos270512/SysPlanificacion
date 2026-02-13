<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$pdo = require_once __DIR__ . '/../config/conexion.php';
$correo = $_SESSION['usuario']['correo'];

$stmt = $pdo->prepare("SELECT codigo, carrera FROM docente WHERE correo = ?");
$stmt->execute([$correo]);
$docente = $stmt->fetch(PDO::FETCH_ASSOC);

$asignaturas = [];
if ($docente) {
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/css/planificaciontyle.css">
</head>
<body>
<div class="container mt-4">
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></h2>
    <p><strong>Correo:</strong> <?php echo htmlspecialchars($_SESSION['usuario']['correo']); ?></p>
    <p><strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['usuario']['rol']); ?></p>

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
  <div class="mb-3" id="btnPlanificacionContainer" style="display:none;">
            <button class="btn btn-success" id="btnGenerarPlanificacion">
                <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                <i class="bi bi-plus-circle"></i> Generar Planificación
            </button>
        </div>
        <br>
<!-- Carrusel de unidades con flechas -->
<h2 class="mb-4">Unidades de la Asignatura</h2>
<div class="mb-3" id="buscadorUnidadesContainer" style="display:none;">
    <input type="text" id="buscadorUnidades" class="form-control form-control-sm" style="max-width: 300px; display: inline-block;" placeholder="Buscar unidad por nombre...">
</div>
<div id="carruselUnidadesContainer" style="display:none; position:relative;">
    <button id="flechaIzquierda" class="btn btn-light shadow-sm flecha-carrusel" style="position:absolute;left:0;top:50%;transform:translateY(-50%);z-index:2;display:none;">
        <i class="bi bi-chevron-left"></i>
    </button>
    <div id="unidadesCarousel" class="unidades-carousel" style="margin:0 40px;">
        <!-- Aquí se insertan las unidades dinámicamente -->
    </div>
    <button id="flechaDerecha" class="btn btn-light shadow-sm flecha-carrusel" style="position:absolute;right:0;top:50%;transform:translateY(-50%);z-index:2;display:none;">
        <i class="bi bi-chevron-right"></i>
    </button>
</div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No tienes asignaturas asignadas.</div>
    <?php endif; ?>
</div>
<script>
    // Variable global con el rol del usuario
    const userRole = <?php echo json_encode($_SESSION['usuario']['rol']); ?>;
</script>
<script>
    // Variables y lógica de asignatura (igual que antes)
    document.addEventListener('DOMContentLoaded', function() {
        const asignaturas = <?php echo json_encode($asignaturas); ?>;
        const carrera = <?php echo json_encode($docente ? $docente['carrera'] : ''); ?>;
        const select = document.getElementById('asignatura');
        const card = document.getElementById('asignaturaCard');
        const cardBody = document.getElementById('asignaturaCardBody');
        const btnPlanificacionContainer = document.getElementById('btnPlanificacionContainer');

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
            <td class="px-3"><strong>Nivel:</strong><br>${asig.nivel ?? ''}</td>
            <td class="px-3"><strong>Jornada:</strong><br>${asig.jornada ?? ''}</td>
        </tr>
        <tr>
            <td class="px-3"><strong>Aula:</strong><br>${asig.aula ?? ''}</td>
            <td class="px-3"><strong>Carrera:</strong><br>${carrera}</td>
            <td class="px-3"><strong>Horario:</strong><br>${asig.horario ?? ''}</td>
            <td class="px-3"><strong>Fecha inicio:</strong><br>${asig.fecha_inicio ? (new Date(asig.fecha_inicio)).toLocaleDateString() : ''}</td>
        </tr>
        <tr>
            <td class="px-3"><strong>Fecha fin:</strong><br>${asig.fecha_fin ? (new Date(asig.fecha_fin)).toLocaleDateString() : ''}</td>
        </tr>
    </table>
    </div>
                `;
                btnPlanificacionContainer.style.display = 'block';
            } else {
                card.style.display = 'none';
                btnPlanificacionContainer.style.display = 'none';
            }
        });

        // Mostrar la card de la primera asignatura por defecto si existe
        if (select.value) {
            select.dispatchEvent(new Event('change'));
        }
    });
</script>
<script src="assets/js/planificaciones.js"></script>
<script src="assets/js/carruselUnidades.js"></script>

</body>
</html>