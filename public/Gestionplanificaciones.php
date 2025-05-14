<?php
$pdo = require_once __DIR__ . '/../config/conexion.php';

// 1. Obtener asignaturas dinámicamente si se pide con AJAX
if (isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];
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
    // Aquí podrías guardar en una tabla de planificaciones
    // por ahora solo mostramos los datos enviados
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
    <!-- JOSE POR FAVOR AQUI TU DEBES DE  CONCENTRARTE EN EL FRONTEND ESTO FUNCIONA BIEN -->
    <!-- Bootstrap 5 CSS -->
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="../assets/css/gestionPlanificaciones.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJx3WQf8K7p2K3bGvX5J1csOeK5C1nZq+dZTgkVfO1zzRaY0lxxw2d34mBgl" crossorigin="anonymous">
    <script>
        function cargarAsignaturas(docenteCodigo) {
            if (!docenteCodigo) return;

            fetch('GestionPlanificaciones.php?codigo=' + docenteCodigo)
                .then(response => response.json())
                .then(data => {
                    const selectAsignatura = document.getElementById('asignatura');
                    const info = document.getElementById('info-asignatura');
                    selectAsignatura.innerHTML = '<option value="">Seleccione</option>';

                    data.forEach(asig => {
                        const option = document.createElement('option');
                        option.value = JSON.stringify(asig); // Guardamos todo el objeto
                        option.textContent = asig.nombre_asignatura;
                        selectAsignatura.appendChild(option);
                    });

                    info.innerHTML = ''; // Limpiar si cambia el docente
                });
        }

        function mostrarDatosAsignatura(valor) {
            const asig = JSON.parse(valor);

            document.getElementById('info-asignatura').innerHTML = `  
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Carrera:</label><input type="text" name="carrera" value="${asig.carrera}" class="form-control" readonly><br>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Jornada:</label><input type="text" name="jornada" value="${asig.jornada}" class="form-control" readonly><br>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Horario:</label><input type="text" name="horario" value="${asig.horario}" class="form-control" readonly><br>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Nivel:</label><input type="text" name="nivel" value="${asig.nivel}" class="form-control" readonly><br>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Aula:</label><input type="text" name="aula" value="${asig.aula}" class="form-control" readonly><br>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Fecha Inicio:</label><input type="text" value="${asig.fecha_inicio}" class="form-control" readonly><br>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Fecha Fin:</label><input type="text" value="${asig.fecha_fin}" class="form-control" readonly><br>
                    </div>
                </div>
            `;
        }
    </script>
</head>

<body>
    <div class="container mt-5">
        <div class="form-container">
            <h2 class="text-center form-title mb-4">Formulario - Información General</h2>
            <form method="POST" action="../app/generarPdf.php" class="shadow-lg p-4 bg-light rounded">
                <div class="mb-3">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" readonly><br>
                </div>

                <div class="mb-3">
                    <label for="docente" class="form-label">Docente</label>
                    <select name="docente" class="form-select" onchange="cargarAsignaturas(this.value)" required>
                        <option value="">Seleccione un docente</option>
                        <?php foreach ($docentes as $docente): ?>
                            <option value="<?= $docente['codigo'] ?>"><?= $docente['nombre'] ?></option>
                        <?php endforeach; ?>
                    </select><br>
                </div>

                <div class="mb-3">
                    <label for="asignatura" class="form-label">Asignatura</label>
                    <select name="asignatura" id="asignatura" class="form-select" onchange="mostrarDatosAsignatura(this.value)" required>
                        <option value="">Seleccione</option>
                    </select><br>
                </div>

                <div id="info-asignatura"></div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">Generar PDF</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0CkMxuW3fj3DgWe2gSYbs4dRvo9KzKq0MbEgtQyMzHmGh6bN" crossorigin="anonymous"></script>
</body>

</html>