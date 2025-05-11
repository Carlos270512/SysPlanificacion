<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'ADMIN') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/adminstyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-dark bg-dark w-100">
        <div class="container-fluid d-flex justify-content-between">
            <a href="admin_dashboard.php" class="navbar-brand d-flex align-items-center">
                <img src="assets/img/logo.png" alt="Logo" height="40" class="me-2">
                <span>Panel Administrador</span>
            </a>
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
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        <div id="sidebar">
            <a href="gestionUsuarios.php" target="mainFrame"><i class="fas fa-user-cog me-2"></i> Gestión de Usuarios</a>
            <a href="subirExcel.php" target="mainFrame"><i class="fas fa-chalkboard-teacher me-2"></i> Cargar Datos</a>
            <a href="Gestionplanificaciones.php" target="mainFrame"><i class="fas fa-book me-2"></i> Gestión de Planificaciones</a>
            <a href="reportes.php" target="mainFrame"><i class="fas fa-file-download me-2"></i> Generar Reportes</a>
            <a href="acerca.php" target="mainFrame"><i class="fas fa-info-circle me-2"></i> Acerca del Sistema</a>
        </div>
        <div id="content">
            <iframe id="mainFrame" name="mainFrame" src="" frameborder="0"></iframe>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>
