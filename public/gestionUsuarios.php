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
} elseif (isset($_GET['archivo_subido'])) {
    $mensaje = '<div class="alert alert-warning">Ya se subió este archivo anteriormente. Por favor, selecciona un archivo diferente.</div>';
}
$hayErrores = isset($_GET['errores']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios (Docentes)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container-fluid mt-5">
    <h2 class="mb-4">Importar Usuarios desde Excel</h2>

    <?php echo $mensaje; ?>

    <form action="../app/procesarIngresoUsuarios.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="archivo_excel" class="form-label">Selecciona el archivo Excel:</label>
            <input class="form-control" type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls" required>
        </div>
        <button class="btn btn-primary mb-4" type="submit" name="submit">Subir</button>
    </form>
    <div class="mb-3">
        <a href="gestionUsuarios.php?estado=ACTIVO" class="btn btn-outline-success btn-sm <?= (!isset($_GET['estado']) || $_GET['estado'] === 'ACTIVO') ? 'active' : '' ?>">Mostrar Activos</a>
        <a href="gestionUsuarios.php?estado=INACTIVO" class="btn btn-outline-secondary btn-sm <?= (isset($_GET['estado']) && $_GET['estado'] === 'INACTIVO') ? 'active' : '' ?>">Mostrar Inactivos</a>
    </div>
    <!-- Tabla para mostrar los usuarios -->
    <div class="table-responsive">
        <table id="usuariosTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Carrera</th>
                    <th>Título</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require __DIR__ . '/../config/conexion.php';
                $estadoFiltro = isset($_GET['estado']) && $_GET['estado'] === 'INACTIVO' ? 'INACTIVO' : 'ACTIVO';
                $stmt = $pdo->prepare("SELECT codigo, carrera, titulo, nombre, correo, rol, estado FROM docente WHERE estado = ?");
                $stmt->execute([$estadoFiltro]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['codigo']) ?></td>
                        <td><?= htmlspecialchars($row['carrera']) ?></td>
                        <td><?= htmlspecialchars($row['titulo']) ?></td>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><?= htmlspecialchars($row['correo']) ?></td>
                        <td><?= htmlspecialchars($row['rol']) ?></td>
                        <td>
                            <span class="badge <?= $row['estado'] === 'ACTIVO' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= htmlspecialchars($row['estado']) ?>
                            </span>
                        </td>
                        <td>
                            <form action="../app/cambiarEstadoUsuario.php" method="POST" style="display:inline;">
                                <input type="hidden" name="codigo" value="<?= htmlspecialchars($row['codigo']) ?>">
                                <input type="hidden" name="estado" value="<?= $row['estado'] === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO' ?>">
                                <button type="submit" class="btn btn-sm <?= $row['estado'] === 'ACTIVO' ? 'btn-danger' : 'btn-success' ?>">
                                    <?= $row['estado'] === 'ACTIVO' ? 'Inactivar' : 'Activar' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($hayErrores && isset($_SESSION['errores_excel']) && !empty($_SESSION['errores_excel'])): ?>
<div class="modal fade" id="erroresModal" tabindex="-1" aria-labelledby="erroresModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="erroresModalLabel">Errores encontrados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>Algunos registros no se pudieron procesar porque tienen campos vacíos o inválidos. Revisa los detalles:</p>
                <div class="table-responsive">
                    <table id="tablaErroresExcel" class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Carrera</th>
                                <th>Título</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Errores</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['errores_excel'] as $err): ?>
                                <tr>
                                    <td><?= htmlspecialchars($err['codigo']) ?></td>
                                    <td><?= htmlspecialchars($err['carrera']) ?></td>
                                    <td><?= htmlspecialchars($err['titulo']) ?></td>
                                    <td><?= htmlspecialchars($err['nombre']) ?></td>
                                    <td><?= htmlspecialchars($err['correo']) ?></td>
                                    <td><?= htmlspecialchars($err['rol']) ?></td>
                                    <td class="text-danger"><?= htmlspecialchars($err['errores']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#tablaErroresExcel').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            pageLength: 5
        });
        var erroresModal = new bootstrap.Modal(document.getElementById('erroresModal'));
        erroresModal.show();
    });
</script>
<?php unset($_SESSION['errores_excel']); ?>
<?php endif; ?>

<script>
    $(document).ready(function () {
        $('#usuariosTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            }
        });
    });
</script>
</body>
</html>