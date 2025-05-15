<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    header("Location: index.php");
    exit();
}

$mensaje = '';
if (isset($_GET['exito'])) {
    $mensaje = '<div class="alert alert-success">Archivo Excel cargado correctamente.</div>';
} elseif (isset($_GET['error'])) {
    $mensaje = '<div class="alert alert-danger">Hubo un error al procesar el archivo.</div>';
} elseif (isset($_GET['error_encabezados'])) {
    $mensaje = '<div class="alert alert-danger">Los encabezados del archivo no son válidos. Por favor, verifica el formato.</div>';
} elseif (isset($_GET['error_subida'])) {
    $mensaje = '<div class="alert alert-danger">Error al subir el archivo. Asegúrate de que sea un archivo Excel válido.</div>';
} elseif (isset($_GET['error_formato'])) {
    $mensaje = '<div class="alert alert-danger">El archivo subido no es un archivo Excel válido. Por favor, verifica el formato.</div>';
} elseif (isset($_GET['archivo_duplicado'])) {
    $mensaje = '<div class="alert alert-warning">Este archivo ya fue subido anteriormente.</div>';
}

$hayErrores = isset($_GET['errores']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Excel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Subir archivo Excel</h2>

        <?php echo $mensaje; ?>

        <form action="../app/procesarExcel.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="archivo_excel" class="form-label">Selecciona el archivo Excel:</label>
                <input class="form-control" type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls" required>
            </div>
            <button class="btn btn-primary mb-4" type="submit" name="submit">Subir</button>
        </form>

        <!-- Tabla para mostrar los datos subidos -->
        <div class="table-responsive">
            <table id="asignaturasTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Asignatura</th>
                        <th>Horario</th>
                        <th>Jornada</th>
                        <th>Aula</th>
                        <th>Nivel</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Codigo Profesor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require __DIR__ . '/../config/conexion.php';
                    $stmt = $pdo->query("SELECT codigo, nombre_asignatura, horario, jornada, aula, nivel, fecha_inicio, fecha_fin, docente_codigo FROM asignatura");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['codigo']) ?></td>
                            <td><?= htmlspecialchars($row['nombre_asignatura']) ?></td>
                            <td><?= htmlspecialchars($row['horario']) ?></td>
                            <td><?= htmlspecialchars($row['jornada']) ?></td>
                            <td><?= htmlspecialchars($row['aula']) ?></td>
                            <td><?= htmlspecialchars($row['nivel']) ?></td>
                            <td><?= htmlspecialchars($row['fecha_inicio']) ?></td>
                            <td><?= htmlspecialchars($row['fecha_fin']) ?></td>
                            <td><?= htmlspecialchars($row['docente_codigo']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#asignaturasTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                }
            });
        });
    </script>
</body>
</html>