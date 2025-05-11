<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    header("Location: index.php");
    exit();
}

$mensaje = '';
if (isset($_GET['exito'])) {
    $mensaje = '<div class="alert alert-success">Usuarios cargados correctamente desde Excel.</div>';
} elseif (isset($_GET['error'])) {
    $mensaje = '<div class="alert alert-danger">Hubo un error al procesar el archivo.</div>';
} elseif (isset($_GET['error_encabezados'])) {
    $mensaje = '<div class="alert alert-danger">Los encabezados del archivo no son válidos.</div>';
} elseif (isset($_GET['error_subida'])) {
    $mensaje = '<div class="alert alert-danger">Error al subir el archivo. Asegúrate de que sea un archivo Excel válido.</div>';
} elseif (isset($_GET['error_formato'])) {
    $mensaje = '<div class="alert alert-danger">El archivo subido no es un archivo Excel válido. Por favor, verifica el formato.</div>';
}
$hayErrores = isset($_GET['errores']);
$archivoSubido = isset($_GET['archivo_subido']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios (Docentes)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Importar Usuarios desde Excel</h2>

        <?php echo $mensaje; ?>

        <form action="../app/procesarIngresoUsuarios.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="archivo_excel" class="form-label">Selecciona el archivo Excel:</label>
                <input class="form-control" type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls" required>
            </div>
            <button class="btn btn-primary" type="submit" name="submit">Subir</button>
        </form>
    </div>

    <?php if ($archivoSubido): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast align-items-center text-bg-warning border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Este archivo ya fue subido anteriormente.
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($hayErrores): ?>
    <div class="modal fade" id="erroresModal" tabindex="-1" aria-labelledby="erroresModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="erroresModalLabel">Errores encontrados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Algunos registros no se pudieron procesar. Puedes descargar el archivo con los errores desde el siguiente enlace:
                    <a href="../storage/uploads/errores.xlsx" class="btn btn-link">Descargar errores.xlsx</a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        var erroresModal = new bootstrap.Modal(document.getElementById('erroresModal'));
        erroresModal.show();
    </script>
    <?php endif; ?>
</body>
</html>