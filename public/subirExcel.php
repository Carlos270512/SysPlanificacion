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
}

$hayErrores = isset($_GET['errores']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Excel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
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
            <button class="btn btn-primary" type="submit" name="submit">Subir</button>
        </form>
    </div>

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
                    <a href="errores.xlsx" class="btn btn-link">Descargar errores.xlsx</a>
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