<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'COORDINADOR') {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Docente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/docenteStyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-dark bg-dark w-100 navbar-expand-lg">
        <div class="container-fluid d-flex justify-content-between">
            <a href="docente_dashboard.php" class="navbar-brand d-flex align-items-center">
                <img src="../assets/img/logotvn.png" alt="Logo" height="40" class="me-2">
                <span>Panel Docente</span>
            </a>
            <ul class="navbar-nav flex-row align-items-center mx-auto">
                <li class="nav-item dropdown mx-2">
                    <a class="nav-link dropdown-toggle custom-nav-link" href="#" id="planificacionDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-cog me-2"></i>Planificación
                    </a>
                    <ul class="dropdown-menu custom-dropdown" aria-labelledby="planificacionDropdown">
                        <li><a class="dropdown-item" href="revisarPlanficaciones.php" target="mainFrame">Revisar Planificación</a></li>
                        <li><a class="dropdown-item" href="../planificaciones.php" target="mainFrame">Generar Planificaciones </a></li>
                    </ul>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link custom-nav-link" href="../reportes.php" target="mainFrame"><i class="fas fa-file-download me-2"></i>Generar Reportes</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link custom-nav-link" href="../acerca.php" target="mainFrame"><i class="fas fa-info-circle me-2"></i>Acerca del Sistema</a>
                </li>
            </ul>
            <div class="dropdown">
                <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle me-2"></i>
                    <span class="text-warning fw-bold"><?php echo $_SESSION['usuario']['rol']; ?></span>, <?php echo $_SESSION['usuario']['nombre']; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-3 py-2">
                        <strong><?php echo $_SESSION['usuario']['nombre']; ?></strong><br>
                        <small class="text-muted"><?php echo $_SESSION['usuario']['correo']; ?></small>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="content" style="height:calc(100vh - 72px);">
        <iframe id="mainFrame" name="mainFrame" src="" frameborder="0" style="width:100%;height:100%;"></iframe>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>