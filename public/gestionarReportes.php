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
    <!-- Opcional: Bootstrap para estilos bonitos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
            // Consultar las unidades asociadas a la asignatura
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
        <!-- ...resto de tu código... -->
    </div>
    <!-- Opcional: Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-unidad').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const idUnidad = this.getAttribute('data-id');
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
                                        <textarea class="form-control" rows="2" readonly>${data.objetivo_unidad || ''}</textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Metodología</label>
                                        <textarea class="form-control" rows="2" readonly>${data.metodologia || ''}</textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Actividades de Recuperación</label>
                                        <textarea class="form-control" rows="2" readonly>${data.actividades_recuperacion || ''}</textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Recursos Didácticos</label>
                                        <textarea class="form-control" rows="2" readonly>${data.recursos_didacticos || ''}</textarea>
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
                                        <a href="agregarSemana.php?id_unidad=${data.id_unidad}" class="btn btn-success">Agregar nueva semana</a>
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
    </script>
</body>
</html>