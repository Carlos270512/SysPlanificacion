<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    header('Location: index.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_database' && isset($_POST['confirm_reset'])) {
    $transactionStarted = false;
    
    try {
        // Verificar que PDO esté disponible
        if (!$pdo) {
            throw new Exception("No se pudo conectar a la base de datos");
        }
        
        // Iniciar transacción
        $result = $pdo->beginTransaction();
        if (!$result) {
            throw new Exception("No se pudo iniciar la transacción");
        }
        $transactionStarted = true;
        
        // Obtener el código del administrador actual para NO eliminarlo
        $admin_actual = $_SESSION['usuario']['codigo'] ?? null;
        
        // Deshabilitar verificación de claves foráneas temporalmente
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // ORDEN CORRECTO DE ELIMINACIÓN (respetando dependencias):
        
        // 1. Eliminar semana_linea (depende de unidad)
        $pdo->exec("DELETE FROM semana_linea");
        //$pdo->exec("ALTER TABLE semana_linea AUTO_INCREMENT = 1");
        
        // 2. Eliminar planificaciones (depende de unidad) - NO planificaciones_repository
        $pdo->exec("DELETE FROM planificaciones");
        //$pdo->exec("ALTER TABLE planificaciones AUTO_INCREMENT = 1");
        
        // 3. Eliminar semana (depende de unidad)
        $pdo->exec("DELETE FROM semana");
        //$pdo->exec("ALTER TABLE semana AUTO_INCREMENT = 1");
        
        // 4. Eliminar unidad (depende de asignatura)
        $pdo->exec("DELETE FROM unidad");
        //$pdo->exec("ALTER TABLE unidad AUTO_INCREMENT = 1");
        
        // 5. Eliminar asignatura (depende de docente)
        $pdo->exec("DELETE FROM asignatura");
        
        // 6. Eliminar docentes EXCEPTO el administrador actual
        if ($admin_actual) {
            $stmt = $pdo->prepare("DELETE FROM docente WHERE codigo != ?");
            $stmt->execute([$admin_actual]);
        } else {
            // Si no hay código específico, eliminar todos los docentes que NO sean ADMIN
            $pdo->exec("DELETE FROM docente WHERE rol != 'ADMIN'");
        }
        
        // Rehabilitar verificación de claves foráneas
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        // Confirmar transacción
        if ($transactionStarted) {
            $pdo->commit();
            $transactionStarted = false;
        }
        
        $message = "¡Nuevo período académico iniciado exitosamente! Se eliminaron todas las planificaciones activas y unidades. Se conservó su cuenta de administrador y el repositorio histórico.";
        
    } catch (Exception $e) {
        // Solo hacer rollback si la transacción fue iniciada y está activa
        if ($transactionStarted) {
            try {
                if ($pdo && $pdo->inTransaction()) {
                    $pdo->rollback();
                }
            } catch (PDOException $rollbackError) {
                // Si falla el rollback, no hacer nada más
                error_log("Error en rollback: " . $rollbackError->getMessage());
            }
        }
        
        // Asegurar que las claves foráneas estén habilitadas
        try {
            if ($pdo) {
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            }
        } catch (PDOException $fkError) {
            // Ignorar errores de FK
            error_log("Error al rehabilitar FK: " . $fkError->getMessage());
        }
        
        $error = "Error al limpiar la base de datos: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Período Académico - Sistema de Planificación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .danger-zone {
            border: 2px solid #dc3545;
            border-radius: 10px;
            background-color: #fff5f5;
        }
        .warning-icon {
            color: #dc3545;
            font-size: 3rem;
        }
        .btn-danger-custom {
            background-color: #dc3545;
            border-color: #dc3545;
            font-weight: 600;
            padding: 12px 30px;
        }
        .btn-danger-custom:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .corporate-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 2rem 0;
        }
        .safe-zone {
            border: 2px solid #28a745;
            border-radius: 10px;
            background-color: #f8fff9;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header Corporativo -->
    <div class="corporate-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0"><i class="fas fa-graduation-cap me-3"></i>Sistema de Planificación Académica</h1>
                    <p class="mb-0 mt-2">Gestión de Períodos Académicos</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-light text-dark fs-6">Administrador: <?php echo $_SESSION['usuario']['nombre'] ?? 'Admin'; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <!-- Mensajes de Estado -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Panel Principal -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-sync-alt me-2"></i>Iniciar Nuevo Período Académico
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-calendar-alt text-primary" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Preparar Sistema para Nuevo Semestre</h4>
                            <p class="text-muted">Esta acción limpiará los datos académicos actuales preservando información crítica del sistema.</p>
                        </div>

                        <!-- Información de lo que se eliminará vs lo que se conserva -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <i class="fas fa-trash-alt text-danger mb-2" style="font-size: 2rem;"></i>
                                        <h6 class="text-danger">Datos que se ELIMINARÁN:</h6>
                                        <ul class="list-unstyled text-start">
                                            <li><i class="fas fa-times text-danger me-2"></i>Todas las asignaturas</li>
                                            <li><i class="fas fa-times text-danger me-2"></i><strong>Todas las unidades</strong></li>
                                            <li><i class="fas fa-times text-danger me-2"></i>Todas las semanas</li>
                                            <li><i class="fas fa-times text-danger me-2"></i><strong>Planificaciones activas</strong></li>
                                            <li><i class="fas fa-times text-danger me-2"></i>Semanas en línea</li>
                                            <li><i class="fas fa-times text-danger me-2"></i>Docentes (excepto usted)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <i class="fas fa-shield-alt text-success mb-2" style="font-size: 2rem;"></i>
                                        <h6 class="text-success">Datos que se CONSERVAN:</h6>
                                        <ul class="list-unstyled text-start">
                                            <li><i class="fas fa-check text-success me-2"></i>Su cuenta de administrador</li>
                                            <li><i class="fas fa-check text-success me-2"></i><strong>Repositorio histórico</strong> (planificaciones_repository)</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Configuraciones del sistema</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Estructura de base de datos</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Zona Segura -->
                        <div class="safe-zone p-3 mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <i class="fas fa-user-shield text-success" style="font-size: 3rem;"></i>
                                </div>
                                <div class="col-md-10">
                                    <h5 class="text-success mb-2">Zona Protegida</h5>
                                    <p class="mb-0">Su cuenta: <strong><?php echo $_SESSION['usuario']['nombre']; ?></strong> (<?php echo $_SESSION['usuario']['codigo']; ?>) será preservada junto con el repositorio histórico de planificaciones.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Zona de Peligro -->
                        <div class="danger-zone p-4">
                            <div class="text-center">
                                <i class="fas fa-exclamation-triangle warning-icon mb-3"></i>
                                <h4 class="text-danger mb-3">¡ZONA DE PELIGRO!</h4>
                                <p class="text-danger fw-bold mb-4">
                                    Esta acción eliminará permanentemente todos los datos académicos del período actual, incluyendo unidades y planificaciones activas.
                                </p>
                                
                                <form method="POST" id="resetForm" class="d-inline">
                                    <input type="hidden" name="action" value="reset_database">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="confirmReset" name="confirm_reset" required>
                                        <label class="form-check-label fw-bold" for="confirmReset">
                                            Confirmo que entiendo que se eliminarán TODAS las unidades y planificaciones activas
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-danger-custom btn-lg" onclick="showFinalConfirmation()">
                                        <i class="fas fa-rocket me-2"></i>INICIAR NUEVO PERÍODO
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="admin_dashboard.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>Cancelar y Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación Final -->
    <div class="modal fade" id="finalConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>CONFIRMACIÓN FINAL
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <i class="fas fa-rocket text-danger mb-3" style="font-size: 4rem;"></i>
                    <h4 class="text-danger mb-3">¿Iniciar nuevo período académico?</h4>
                    <p class="fs-5 mb-4">Se eliminarán <strong>UNIDADES y PLANIFICACIONES</strong> actuales.</p>
                    <div class="alert alert-success">
                        <strong>Se conservarán:</strong> Su cuenta y el repositorio histórico
                    </div>
                    <p class="text-muted">Para confirmar, escriba: <strong>ELIMINAR TODO</strong></p>
                    <input type="text" class="form-control form-control-lg text-center mb-3" 
                           id="confirmText" placeholder="Escriba: ELIMINAR TODO">
                    <div class="text-danger mb-3" id="confirmError" style="display: none;">
                        El texto no coincide. Debe escribir exactamente: ELIMINAR TODO
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger btn-lg" onclick="executeReset()">
                        <i class="fas fa-rocket me-2"></i>¡INICIAR NUEVO PERÍODO!
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showFinalConfirmation() {
            const checkbox = document.getElementById('confirmReset');
            if (!checkbox.checked) {
                alert('Debe marcar la casilla de confirmación primero.');
                return;
            }
            
            const modal = new bootstrap.Modal(document.getElementById('finalConfirmModal'));
            modal.show();
        }

        function executeReset() {
            const confirmText = document.getElementById('confirmText').value;
            const errorDiv = document.getElementById('confirmError');
            
            if (confirmText !== 'ELIMINAR TODO') {
                errorDiv.style.display = 'block';
                return;
            }
            
            // Ocultar modal y enviar formulario
            const modal = bootstrap.Modal.getInstance(document.getElementById('finalConfirmModal'));
            modal.hide();
            
            // Mostrar loading
            const btn = document.querySelector('button[onclick="executeReset()"]');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
            btn.disabled = true;
            
            setTimeout(() => {
                document.getElementById('resetForm').submit();
            }, 1000);
        }

        // Limpiar el modal cuando se cierre
        document.getElementById('finalConfirmModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('confirmText').value = '';
            document.getElementById('confirmError').style.display = 'none';
        });
    </script>
</body>
</html>