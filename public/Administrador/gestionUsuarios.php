<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    header("Location: ../index.php");
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
    //$mensaje = '<div class="alert alert-warning">Ya se subió este archivo anteriormente. Por favor, selecciona un archivo diferente.</div>';
}
$hayErrores = isset($_GET['errores']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios (Docentes)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/gestionUsuariosSytles.css">
    <script src="../assets/js/Administrador/toast.js"></script>
</head>

<body>
    <div class="container-fluid mt-5">
        <h2 class="mb-4">Subir Docentes</h2>
        <form action="../../app/Operaciones/procesarIngresoUsuarios.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="archivo_excel" class="form-label">Selecciona el archivo Excel:</label>
                <input class="form-control" type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx, .xls" required>
            </div>
            <button class="btn btn-cafe mb-4" type="submit" name="submit">
                <i class="fas fa-upload me-1"></i> Subir
            </button>
            <button type="button" class="btn btn-success mb-4 ms-2" data-bs-toggle="modal" data-bs-target="#registrarDocenteModal">
                <i class="fas fa-user-plus me-1"></i> Registrar
            </button>
        </form>

        <!-- Modal Registrar Docente -->
        <div class="modal fade" id="registrarDocenteModal" tabindex="-1" aria-labelledby="registrarDocenteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" action="../../app/Operaciones/registrarDocenteManual.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registrarDocenteModalLabel">Registrar Docente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="codigo" class="form-label">Código</label>
                                    <input type="text" class="form-control" name="codigo" id="codigo" placeholder="Ej: AMPU" required>
                                    <small id="error_codigo" class="form-text text-danger d-none">Solo se permiten letras</small>
                                </div>
                                <div class="mb-3">
                                    <label for="carrera" class="form-label">Carrera</label>
                                    <input type="text" class="form-control" name="carrera" id="carrera" placeholder="Ej: Desarrollo de Software" required>
                                    <small id="error_carrera" class="form-text text-danger d-none">Solo se permiten letras</small>
                                </div>
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ej: Juan Pérez" required>
                                    <small id="error_nombre" class="form-text text-danger d-none">Solo se permiten letras</small>
                                </div>
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título</label>
                                    <input type="text" class="form-control" name="titulo" id="titulo" placeholder="Ej: Ingeniero" required>
                                    <small id="error_titulo" class="form-text text-danger d-none">Solo se permiten letras</small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">

                                <div class="mb-3">
                                    <label for="rol" class="form-label">Rol</label>
                                    <select class="form-control" name="rol" id="rol" required>
                                        <option value="">Seleccione un rol</option>
                                        <option value="DOCENTE">DOCENTE</option>
                                        <option value="COORDINADOR">COORDINADOR</option>
                                        <option value="ADMIN">ADMIN</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="correo" class="form-label">Correo</label>
                                    <input type="email" class="form-control" name="correo" id="correo" placeholder="Ej: juan@istvidanueva.edu.ec" required>
                                    <small id="error_correo" class="form-text text-danger d-none">Ingrese un correo electrónico válido</small>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" name="password" id="password" placeholder="Ej: 1234" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-cafe">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
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
                    require __DIR__ . '/../../config/conexion.php';
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
                                <form action="../../app/Operaciones/cambiarEstadoUsuario.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($row['codigo']) ?>">
                                    <input type="hidden" name="estado" value="<?= $row['estado'] === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO' ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
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
                        <a href="../../app/Operaciones/descargarErroresUsuariosExcel.php" class="btn btn-success">
                            <i class="fas fa-file-excel me-1"></i> Descargar errores en Excel
                        </a>
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
        <?php // unset($_SESSION['errores_excel']); 
        ?>
    <?php endif; ?>

    <script>
        $(document).ready(function() {
            $('#usuariosTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                }
            });

            // Validación en tiempo real para solo letras en código (Registrar Docente)
            $('#codigo').on('input', function() {
                let valor = $(this).val();
                // Solo letras (mayúsculas/minúsculas, tildes y ñ)
                if (!/^[a-zA-ZÁÉÍÓÚáéíóúÑñ]*$/.test(valor)) {
                    $('#codigo').addClass('is-invalid');
                    $('#error_codigo').removeClass('d-none');
                } else {
                    $('#codigo').removeClass('is-invalid');
                    $('#error_codigo').addClass('d-none');
                }
                // Transformar a mayúsculas
                $(this).val(valor.toUpperCase());
            });

            // Validación en tiempo real para solo letras en carrera (mayúsculas y minúsculas, sin transformar)
            $('#carrera').on('input', function() {
                let valor = $(this).val();
                if (!/^[a-zA-ZÁÉÍÓÚáéíóúÑñ ]*$/.test(valor)) {
                    $('#carrera').addClass('is-invalid');
                    $('#error_carrera').removeClass('d-none');
                } else {
                    $('#carrera').removeClass('is-invalid');
                    $('#error_carrera').addClass('d-none');
                }
            });

            // Validación en tiempo real para solo letras en nombre (mayúsculas y minúsculas, sin transformar)
            $('#nombre').on('input', function() {
                let valor = $(this).val();
                if (!/^[a-zA-ZÁÉÍÓÚáéíóúÑñ ]*$/.test(valor)) {
                    $('#nombre').addClass('is-invalid');
                    $('#error_nombre').removeClass('d-none');
                } else {
                    $('#nombre').removeClass('is-invalid');
                    $('#error_nombre').addClass('d-none');
                }
            });

            // Validación en tiempo real para solo letras en título (mayúsculas y minúsculas, sin transformar)
            $('#titulo').on('input', function() {
                let valor = $(this).val();
                if (!/^[a-zA-ZÁÉÍÓÚáéíóúÑñ ]*$/.test(valor)) {
                    $('#titulo').addClass('is-invalid');
                    $('#error_titulo').removeClass('d-none');
                } else {
                    $('#titulo').removeClass('is-invalid');
                    $('#error_titulo').addClass('d-none');
                }
            });

            // Validación en tiempo real para correo electrónico válido
            $('#correo').on('input', function() {
                let valor = $(this).val();
                // Expresión regular básica para validar correo
                let correoValido = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!correoValido.test(valor)) {
                    $('#correo').addClass('is-invalid');
                    $('#error_correo').removeClass('d-none');
                } else {
                    $('#correo').removeClass('is-invalid');
                    $('#error_correo').addClass('d-none');
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
            $toastTitle = 'Usuarios cargados correctamente desde Excel.';
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
            $toastTitle = 'Los encabezados del archivo no son válidos.';
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
        } elseif (isset($_GET['archivo_subido'])) {
            $toastType = 'info';
            $toastTitle = 'Ya se subió este archivo anteriormente. Por favor, selecciona un archivo diferente.';
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