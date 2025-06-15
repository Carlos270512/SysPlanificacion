<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'COORDINADOR') {
    header("Location: ../index.php");
    exit();
}

$pdo = require_once __DIR__ . '/../../config/conexion.php';

// Obtener todos los docentes activos
$stmt = $pdo->query("SELECT codigo, nombre FROM docente WHERE estado = 'ACTIVO' ORDER BY nombre");
$docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si se seleccionó un docente, obtener sus asignaturas
$asignaturas = [];
$docenteSeleccionado = null;
if (isset($_GET['docente_codigo']) && $_GET['docente_codigo'] !== '') {
    $codigoDocente = $_GET['docente_codigo'];
    $stmt2 = $pdo->prepare("SELECT * FROM asignatura WHERE docente_codigo = ?");
    $stmt2->execute([$codigoDocente]);
    $asignaturas = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Obtener datos del docente seleccionado
    foreach ($docentes as $d) {
        if ($d['codigo'] === $codigoDocente) {
            $docenteSeleccionado = $d;
            break;
        }
    }
}

// Si se seleccionó una asignatura, obtener sus datos
$asignaturaSeleccionada = null;
if (isset($_GET['asignatura_codigo']) && $_GET['asignatura_codigo'] !== '') {
    foreach ($asignaturas as $a) {
        if ($a['codigo'] === $_GET['asignatura_codigo']) {
            $asignaturaSeleccionada = $a;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Revisar Planificaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-search me-2"></i>Revisar Planificaciones</h4>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="docente_codigo" class="form-label fw-semibold">Seleccione Docente</label>
                    <select class="form-select" id="docente_codigo" name="docente_codigo" required onchange="this.form.submit()">
                        <option value="">-- Seleccione --</option>
                        <?php foreach ($docentes as $doc): ?>
                            <option value="<?php echo htmlspecialchars($doc['codigo']); ?>" <?php if(isset($codigoDocente) && $codigoDocente == $doc['codigo']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($doc['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($asignaturas): ?>
                <div class="col-md-6">
                    <label for="asignatura_codigo" class="form-label fw-semibold">Asignaturas del Docente</label>
                    <select class="form-select" id="asignatura_codigo" name="asignatura_codigo" onchange="this.form.submit()">
                        <option value="">-- Seleccione --</option>
                        <?php foreach ($asignaturas as $asig): ?>
                            <option value="<?php echo htmlspecialchars($asig['codigo']); ?>" <?php if(isset($_GET['asignatura_codigo']) && $_GET['asignatura_codigo'] == $asig['codigo']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($asig['nombre_asignatura']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </form>

            <?php if ($asignaturaSeleccionada): ?>
                <div class="card border-primary mt-4">
                    <div class="card-header bg-primary text-white">
                        <strong>Datos de la Asignatura</strong>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Nombre:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['nombre_asignatura']); ?></div>
                            <div class="col-md-6"><strong>Código:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['codigo']); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Horario:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['horario']); ?></div>
                            <div class="col-md-4"><strong>Jornada:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['jornada']); ?></div>
                            <div class="col-md-4"><strong>Aula:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['aula']); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4"><strong>Nivel:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['nivel']); ?></div>
                            <div class="col-md-4"><strong>Fecha Inicio:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['fecha_inicio']); ?></div>
                            <div class="col-md-4"><strong>Fecha Fin:</strong> <?php echo htmlspecialchars($asignaturaSeleccionada['fecha_fin']); ?></div>
                        </div>
                        <form action="revisarPlanificacionesAsignatura.php" method="post" class="mt-4">
                            <input type="hidden" name="docente_codigo" value="<?php echo htmlspecialchars($docenteSeleccionado['codigo']); ?>">
                            <input type="hidden" name="asignatura_codigo" value="<?php echo htmlspecialchars($asignaturaSeleccionada['codigo']); ?>">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-eye"></i> Revisar Planificaciones
                            </button>
                        </form>
                    </div>
                </div>
            <?php elseif ($docenteSeleccionado): ?>
                <div class="alert alert-info mt-4">Seleccione una asignatura para ver los detalles.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>