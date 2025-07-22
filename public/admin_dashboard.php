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
    <!-- Botón flotante redondo con logo -->
    <button class="btn floating-logo-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#logoOffcanvas" aria-controls="logoOffcanvas">
        <img src="assets/img/logotvn.png" alt="Logo" class="floating-logo-img">
    </button>

    <!-- Canvas de Bootstrap -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="logoOffcanvas" aria-labelledby="logoOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="logoOffcanvasLabel">Menú Principal</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <!-- Aquí pondrás el contenido que quieras en el canvas -->
            <p>Contenido del canvas...</p>
        </div>
    </div>

    <nav class="navbar navbar-dark bg-dark w-100 navbar-expand-lg">
        <div class="container-fluid d-flex justify-content-between">
            <a href="admin_dashboard.php" class="navbar-brand d-flex align-items-center ms-auto">
                <img src="assets/img/logotvn.png" alt="Logo" style="height: 40px; max-width: 100%; object-fit: contain;" class="me-2">
                <span>Panel Administrador</span>
            </a>
            <ul class="navbar-nav flex-row align-items-center mx-auto">
                <li class="nav-item dropdown mx-2">
                    <a class="nav-link dropdown-toggle custom-nav-link" href="#" id="usuariosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-cog me-2"></i>Usuarios
                    </a>
                    <ul class="dropdown-menu custom-dropdown" aria-labelledby="usuariosDropdown">
                        <li><a class="dropdown-item" href="Administrador/gestionUsuarios.php" target="mainFrame">Gestión de Usuarios</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown mx-2">
                    <a class="nav-link dropdown-toggle custom-nav-link" href="#" id="datosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Datos
                    </a>
                    <ul class="dropdown-menu custom-dropdown" aria-labelledby="datosDropdown">
                        <li><a class="dropdown-item" href="Administrador/subirExcel.php" target="mainFrame">Cargar Asignaturas</a></li>
                        <li><a class="dropdown-item" href="Gestionplanificaciones.php" target="mainFrame">Gestión de Planificaciones</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown mx-2">
                    <a class="nav-link dropdown-toggle custom-nav-link" href="#" id="planificacionesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-book me-2"></i>Planificaciones
                    </a>
                    <ul class="dropdown-menu custom-dropdown" aria-labelledby="planificacionesDropdown">
                        <li><a class="dropdown-item" href="Administrador/Repositorioplanificaciones.php" target="mainFrame">Repositorio de Planificaciones</a></li>
                        <li><a class="dropdown-item" href="planificaciones.php" target="mainFrame">Planificaciones</a></li>
                        <li><a class="dropdown-item" href="nuevoPeriodoAcademico.php" target="mainFrame">Comenzar Nuevo Período </a></li>
                    </ul>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link custom-nav-link" href="#" title="Acerca del Sistema"><i class="fas fa-info-circle me-2"></i></a>
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
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
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