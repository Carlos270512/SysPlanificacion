<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'COORDINADOR') {
    header("Location: ../index.php");
    exit();
}

// Conexión a la base de datos
$pdo = require_once __DIR__ . '/../../config/conexion.php';

// Obtener todos los PDFs de coevaluación
$stmt = $pdo->prepare("
    SELECT 
        id_coevaluacion_repository,
        nombre_archivo,
        asignatura,
        periodo_lectivo,
        fecha_creacion,
        usuario_creacion
    FROM coevaluacion_repository 
    ORDER BY fecha_creacion DESC
");
$stmt->execute();
$pdfs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar descarga de PDF
if (isset($_GET['download']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("
        SELECT nombre_archivo, archivo_pdf, tipo_mime 
        FROM coevaluacion_repository 
        WHERE id_coevaluacion_repository = ?
    ");
    $stmt->execute([$id]);
    $pdf = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pdf) {
        header('Content-Type: ' . $pdf['tipo_mime']);
        header('Content-Disposition: attachment; filename="' . $pdf['nombre_archivo'] . '"');
        header('Content-Length: ' . strlen($pdf['archivo_pdf']));
        echo $pdf['archivo_pdf'];
        exit();
    } else {
        $error = "PDF no encontrado";
    }
}

// Manejar visualización de PDF
if (isset($_GET['view']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("
        SELECT nombre_archivo, archivo_pdf, tipo_mime 
        FROM coevaluacion_repository 
        WHERE id_coevaluacion_repository = ?
    ");
    $stmt->execute([$id]);
    $pdf = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pdf) {
        header('Content-Type: ' . $pdf['tipo_mime']);
        header('Content-Disposition: inline; filename="' . $pdf['nombre_archivo'] . '"');
        echo $pdf['archivo_pdf'];
        exit();
    } else {
        $error = "PDF no encontrado";
    }
}

// Manejar eliminación de PDF
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM coevaluacion_repository WHERE id_coevaluacion_repository = ?");
    if ($stmt->execute([$id])) {
        $success = "PDF eliminado exitosamente";
        // Refrescar la página
        header("Location: ver_coevaluacion.php");
        exit();
    } else {
        $error = "Error al eliminar el PDF";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repositorio de Coevaluaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/coevaluacionStyle.css?v=2">
   <style>
        .pdf-icon {
            color: #dc3545;
            font-size: 1.5rem;
        }
        .action-buttons .btn {
            margin: 2px;
        }
        .table-responsive {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header-section {
            background: linear-gradient(135deg, rgb(44, 64, 115), #0056b3);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #6B3F13;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-folder-open"></i> Repositorio de Coevaluaciones</h1>
                    <p class="mb-0">Gestiona y descarga los documentos PDF de planificación de coevaluaciones</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="coevaluacion_inicio.php" class="btn btn-light btn-lg">
                        <i class="fas fa-plus"></i> Nueva Coevaluación
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="stat-number"><?php echo count($pdfs); ?></div>
                    <div class="text-muted">Total Documentos</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="stat-number">
                        <?php 
                        $hoy = date('Y-m-d');
                        $hoyCount = array_filter($pdfs, function($pdf) use ($hoy) {
                            return date('Y-m-d', strtotime($pdf['fecha_creacion'])) === $hoy;
                        });
                        echo count($hoyCount);
                        ?>
                    </div>
                    <div class="text-muted">Creados Hoy</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="stat-number">
                        <?php 
                        $periodos = array_unique(array_column($pdfs, 'periodo_lectivo'));
                        echo count($periodos);
                        ?>
                    </div>
                    <div class="text-muted">Períodos Lectivos</div>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tabla de PDFs -->
        <div class="table-responsive">
            <h3 class="mb-4"><i class="fas fa-file-pdf text-danger"></i> Documentos PDF</h3>
            
            <?php if (empty($pdfs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-5x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay documentos disponibles</h4>
                    <p class="text-muted">Aún no se han generado documentos de coevaluación.</p>
                    <a href="coevaluacion_inicio.php" class="btn btn-cafe btn-lg">
                        <i class="fas fa-plus"></i> Crear Primera Coevaluación
                    </a>
                </div>
            <?php else: ?>
                <table id="tablaPDFs" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-file-pdf"></i> Archivo</th>
                            <th><i class="fas fa-book"></i> Asignatura</th>
                            <th><i class="fas fa-calendar"></i> Período</th>
                            <th><i class="fas fa-clock"></i> Fecha Creación</th>
                            <th><i class="fas fa-user"></i> Creado Por</th>
                            <th><i class="fas fa-cogs"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pdfs as $pdf): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary"><?php echo $pdf['id_coevaluacion_repository']; ?></span>
                                </td>
                                <td>
                                    <i class="fas fa-file-pdf pdf-icon"></i>
                                    <strong><?php echo htmlspecialchars($pdf['nombre_archivo']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($pdf['asignatura'] ?: 'Sin asignatura'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($pdf['periodo_lectivo']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($pdf['fecha_creacion'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <?php echo htmlspecialchars($pdf['usuario_creacion']); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <!-- Ver PDF -->
                                    <a href="?view=1&id=<?php echo $pdf['id_coevaluacion_repository']; ?>" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="Ver PDF" 
                                       target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <!-- Descargar PDF -->
                                    <a href="?download=1&id=<?php echo $pdf['id_coevaluacion_repository']; ?>" 
                                       class="btn btn-outline-success btn-sm" 
                                       title="Descargar PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    
                                    <!-- Eliminar PDF -->
                                    <button class="btn btn-outline-danger btn-sm" 
                                            title="Eliminar PDF"
                                            onclick="confirmarEliminacion(<?php echo $pdf['id_coevaluacion_repository']; ?>, '<?php echo addslashes($pdf['nombre_archivo']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Botones de acción general -->
        <div class="row mt-4">
            <div class="col-md-6">
                <a href="coevaluacion_inicio.php" class="btn btn-cafe">
                    <i class="fas fa-plus"></i> Nueva Coevaluación
                </a>
                <a href="../coordinador.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Panel
                </a>
            </div>
            <div class="col-md-6 text-end">
                <?php if (!empty($pdfs)): ?>
                    <button class="btn btn-info" onclick="descargarTodos()">
                        <i class="fas fa-download"></i> Descargar Todos
                    </button>
                    <button class="btn btn-warning" onclick="limpiarRepositorio()">
                        <i class="fas fa-broom"></i> Limpiar Repositorio
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Inicializar DataTable
        $(document).ready(function() {
            $('#tablaPDFs').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[4, 'desc']], // Ordenar por fecha de creación descendente
                pageLength: 10,
                responsive: true,
                columnDefs: [
                    { targets: [6], orderable: false } // No ordenar columna de acciones
                ]
            });
        });

        // Confirmar eliminación
        function confirmarEliminacion(id, nombreArchivo) {
            Swal.fire({
                title: '¿Estás seguro?',
                html: `¿Deseas eliminar el archivo:<br><strong>${nombreArchivo}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?delete=1&id=${id}`;
                }
            });
        }

        // Descargar todos los PDFs
        function descargarTodos() {
            Swal.fire({
                title: 'Descargando...',
                text: 'Se abrirán múltiples ventanas para descargar todos los PDFs',
                icon: 'info',
                showConfirmButton: false,
                timer: 3000
            });

            // Obtener todos los IDs y descargar uno por uno
            <?php if (!empty($pdfs)): ?>
                <?php foreach ($pdfs as $pdf): ?>
                    setTimeout(() => {
                        window.open('?download=1&id=<?php echo $pdf['id_coevaluacion_repository']; ?>', '_blank');
                    }, <?php echo array_search($pdf, $pdfs) * 500; ?>); // Delay de 500ms entre descargas
                <?php endforeach; ?>
            <?php endif; ?>
        }

        // Limpiar repositorio
        function limpiarRepositorio() {
            Swal.fire({
                title: '⚠️ ¡ATENCIÓN!',
                html: `
                    <div class="alert alert-danger">
                        <strong>Esta acción eliminará TODOS los documentos PDF del repositorio.</strong><br>
                        Esta operación NO se puede deshacer.
                    </div>
                    <p>¿Estás completamente seguro de que deseas continuar?</p>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-broom"></i> Sí, limpiar todo',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                input: 'text',
                inputPlaceholder: 'Escribe "ELIMINAR TODO" para confirmar',
                inputValidator: (value) => {
                    if (value !== 'ELIMINAR TODO') {
                        return 'Debes escribir exactamente "ELIMINAR TODO" para confirmar';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí puedes agregar la lógica para eliminar todos los registros
                    Swal.fire(
                        'Función no implementada',
                        'La función de limpiar repositorio está pendiente de implementación',
                        'info'
                    );
                }
            });
        }

        // Mostrar toast de éxito al cargar si hay parámetro success
        <?php if (isset($_GET['success'])): ?>
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                icon: 'success',
                title: 'Operación exitosa'
            });
        <?php endif; ?>
    </script>
</body>
</html>