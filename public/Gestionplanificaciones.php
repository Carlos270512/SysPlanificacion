
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

// 2. Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    exit;
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
    <script>
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
                });
        }

        function mostrarDatosAsignatura(valor) {
            if (!valor) {
                document.getElementById('info-asignatura').innerHTML = '';
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
        }
    </script>
</head>

<body>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white text-center">
                <h2 class="form-title mb-0">Formulario - Información General</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="../app/generarPdf.php">
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

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Generar PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>