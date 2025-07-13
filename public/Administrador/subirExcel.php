<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    header("Location: ../index.php");
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/subirExcelstyles.css">
    <script src="../assets/js/Administrador/toast.js"></script>
</head>

<body>
    <h2 class="mb-4">Subir archivo Excel</h2>
    <form action="../../app/Operaciones/procesarExcel.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="archivo_excel" class="form-label">Selecciona el archivo Excel:</label>
            <input class="form-control" type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls" required>
        </div>
        <button class="btn btn-cafe mb-4" type="submit" name="submit">Subir</button>
    </form>

    <!-- Tabla para mostrar los datos subidos (agregada columna Periodo Lectivo) -->
    <div class="table-responsive">
        <table id="asignaturasTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Asignatura</th>
                    <th>Horario</th>
                    <th>Jornada</th>
                    <th>Periodo Lectivo</th>
                    <th>Aula</th>
                    <th>Nivel</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Codigo Profesor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require __DIR__ . '/../../config/conexion.php';
                $stmt = $pdo->query("SELECT codigo, nombre_asignatura, horario, jornada, periodo_academico, aula, nivel, fecha_inicio, fecha_fin, docente_codigo FROM asignatura");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['codigo']) ?></td>
                        <td><?= htmlspecialchars($row['nombre_asignatura']) ?></td>
                        <td><?= htmlspecialchars($row['horario']) ?></td>
                        <td><?= htmlspecialchars($row['jornada']) ?></td>
                        <td><?= htmlspecialchars($row['periodo_academico']) ?></td>
                        <td><?= htmlspecialchars($row['aula']) ?></td>
                        <td><?= htmlspecialchars($row['nivel']) ?></td>
                        <td><?= htmlspecialchars($row['fecha_inicio']) ?></td>
                        <td><?= htmlspecialchars($row['fecha_fin']) ?></td>
                        <td><?= htmlspecialchars($row['docente_codigo'] ?? '') ?></td>
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
                                        <th>Asignatura</th>
                                        <th>Horario</th>
                                        <th>Jornada</th>
                                        <th>Periodo Lectivo</th>
                                        <th>Aula</th>
                                        <th>Nivel</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th>Profesor</th>
                                        <th>Errores</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($_SESSION['errores_excel'] as $err): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($err['codigo']) ?></td>
                                            <td><?= htmlspecialchars($err['asignatura']) ?></td>
                                            <td><?= htmlspecialchars($err['horario']) ?></td>
                                            <td><?= htmlspecialchars($err['jornada']) ?></td>
                                            <td><?= htmlspecialchars($err['periodo_lectivo']) ?></td>
                                            <td><?= htmlspecialchars($err['aula']) ?></td>
                                            <td><?= htmlspecialchars($err['nivel']) ?></td>
                                            <td><?= htmlspecialchars($err['fecha_inicio']) ?></td>
                                            <td><?= htmlspecialchars($err['fecha_fin']) ?></td>
                                            <td><?= htmlspecialchars($err['profesor']) ?></td>
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
            $(document).ready(function() {
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
        $(document).ready(function() {
            $('#asignaturasTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                }
            });
        });
    </script>
    <?php if (!empty($mensaje)): ?>
        <?php
        // Determina los valores para el toast
        $toastType = '';
        $toastTitle = '';
        $toastIconColor = '';
        $toastPopupClass = '';
        $toastTimer = 4000;

        if (isset($_GET['exito'])) {
            $toastType = 'success';
            $toastTitle = 'Archivo Excel cargado correctamente.';
            $toastIconColor = '#fff';
            $toastPopupClass = 'bg-success text-white';
            $toastTimer = 4000;
        } elseif (isset($_GET['error'])) {
            $toastType = 'error';
            $toastTitle = 'Hubo un error al procesar el archivo.';
            $toastIconColor = '#fff';
            $toastPopupClass = 'bg-danger text-white';
            $toastTimer = 5000;
        } elseif (isset($_GET['error_encabezados'])) {
            $toastType = 'warning';
            $toastTitle = 'Los encabezados del archivo no son válidos. Por favor, verifica el formato.';
            $toastIconColor = '#664d03';
            $toastPopupClass = 'bg-warning text-dark';
            $toastTimer = 6000;
        } elseif (isset($_GET['error_subida'])) {
            $toastType = 'error';
            $toastTitle = 'Error al subir el archivo. Asegúrate de que sea un archivo Excel válido.';
            $toastIconColor = '#fff';
            $toastPopupClass = 'bg-danger text-white';
            $toastTimer = 6000;
        } elseif (isset($_GET['error_formato'])) {
            $toastType = 'warning';
            $toastTitle = 'El archivo subido no es un archivo Excel válido. Por favor, verifica el formato.';
            $toastIconColor = '#664d03';
            $toastPopupClass = 'bg-warning text-dark';
            $toastTimer = 6000;
        } elseif (isset($_GET['archivo_duplicado'])) {
            $toastType = 'info';
            $toastTitle = 'Este archivo ya fue subido anteriormente.';
            $toastIconColor = '#055160';
            $toastPopupClass = 'bg-info text-dark';
            $toastTimer = 5000;
        }
        ?>
        <script>
            window.toastType = "<?= $toastType ?>";
            window.toastTitle = "<?= $toastTitle ?>";
            window.toastIconColor = "<?= $toastIconColor ?>";
            window.toastPopupClass = "<?= $toastPopupClass ?>";
            window.toastTimer = <?= $toastTimer ?>;
        </script>
    <?php endif; ?>
</body>

</html>