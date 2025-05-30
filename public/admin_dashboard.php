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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark w-100 custom-navbar">
        <div class="container-fluid">
            <a href="admin_dashboard.php" class="navbar-brand d-flex align-items-center">
                <img src="assets/img/logotvn.png" alt="Logo" style="height: 40px; max-width: 100%; object-fit: contain;" class="me-2">
                <span>Panel Administrador</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav mx-auto flex-row">
                    <!-- Usuarios -->
                    <li class="nav-item dropdown mx-2">
                        <a class="nav-link dropdown-toggle custom-nav-link" href="#" id="usuariosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-cog me-2"></i>Usuarios
                        </a>
                        <ul class="dropdown-menu custom-dropdown" aria-labelledby="usuariosDropdown">
                            <li><a class="dropdown-item" href="gestionUsuarios.php" target="mainFrame">Gestión de Usuarios</a></li>
                        </ul>
                    </li>
                    <!-- Datos -->
                    <li class="nav-item dropdown mx-2">
                        <a class="nav-link dropdown-toggle custom-nav-link" href="#" id="datosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Datos
                        </a>
                        <ul class="dropdown-menu custom-dropdown" aria-labelledby="datosDropdown">
                            <li><a class="dropdown-item" href="subirExcel.php" target="mainFrame">Cargar Datos</a></li>
                        </ul>
                    </li>
                    <!-- Planificaciones -->
                    <li class="nav-item dropdown mx-2">
                        <a class="nav-link dropdown-toggle custom-nav-link" href="#" id="planificacionesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-book me-2"></i>Planificaciones
                        </a>
                        <ul class="dropdown-menu custom-dropdown" aria-labelledby="planificacionesDropdown">
                            <li><a class="dropdown-item" href="Gestionplanificaciones.php" target="mainFrame">Gestión de Planificaciones</a></li>
                            <li><a class="dropdown-item" href="planificaciones.php" target="mainFrame">Planificaciones</a></li>
                        </ul>
                    </li>
                    <!-- Acerca del sistema solo icono -->
                    <li class="nav-item mx-2 d-flex align-items-center">
                        <a class="nav-link custom-nav-link" href="acerca.php" target="mainFrame" title="Acerca del Sistema">
                            <i class="fas fa-info-circle"></i>
                        </a>
                    </li>
                </ul>
                <div class="dropdown ms-auto">
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
        </div>
    </nav>

    <div id="content">
        <iframe id="mainFrame" name="mainFrame" src="admin_dashboard.php" frameborder="0" style="width:100%;height:100%;"></iframe>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    // Al cargar la página, si hay una URL guardada, la usamos
    window.addEventListener('DOMContentLoaded', function() {
        var lastSrc = localStorage.getItem('iframeSrc');
        if (lastSrc) {
            document.getElementById('mainFrame').src = lastSrc;
        }
    });

    // Cada vez que el iframe cambie de página, guardamos la URL
    document.getElementById('mainFrame').addEventListener('load', function() {
        try {
            // Solo guardar si es del mismo dominio
            var current = this.contentWindow.location.pathname + this.contentWindow.location.search;
            localStorage.setItem('iframeSrc', current);
        } catch (e) {
            // Ignorar errores de cross-origin
        }
    });
    </script>
</body>
</html>