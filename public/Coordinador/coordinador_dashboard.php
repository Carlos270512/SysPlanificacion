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
                    <a class="nav-link custom-nav-link" href="../acerca.php" target="mainFrame"><i class="fas fa-info-circle me-2"></i></a>
                </li>


                <!-- NUEVA CAMPANITA DE NOTIFICACIONES -->
                <li class="nav-item dropdown mx-2">
                    <a class="nav-link custom-nav-link position-relative" href="#" id="notificacionesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fa-lg"></i>
                        <span id="badge-notificaciones" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7em; display: none;">
                            0
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end custom-dropdown" aria-labelledby="notificacionesDropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                        <li class="dropdown-header">
                            <strong>Observaciones Pendientes</strong>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <div id="lista-notificaciones">
                            <li class="text-center p-3">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </li>
                        </div>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="text-center p-2">
                            <a href="#" class="btn btn-sm btn-outline-primary" onclick="marcarTodasLeidas()">Marcar todas como leídas</a>
                        </li>
                    </ul>
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
    <script>
        // Variables globales
        let notificacionesInterval;

        // Cargar notificaciones al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarNotificaciones();
            // Actualizar cada 30 segundos
            notificacionesInterval = setInterval(cargarNotificaciones, 30000);
        });

        // Función para cargar notificaciones
        async function cargarNotificaciones() {
            try {
                const response = await fetch('../../app/Notificaciones/NotificacionesService.php?action=obtener');
                const data = await response.json();

                if (data.success) {
                    actualizarBadge(data.total);
                    mostrarNotificaciones(data.notificaciones);
                }
            } catch (error) {
                console.error('Error al cargar notificaciones:', error);
            }
        }

        // Actualizar badge de notificaciones
        function actualizarBadge(total) {
            const badge = document.getElementById('badge-notificaciones');
            if (total > 0) {
                badge.textContent = total > 99 ? '99+' : total;
                badge.style.display = 'block';
                badge.classList.add('notification-badge');
            } else {
                badge.style.display = 'none';
                badge.classList.remove('notification-badge');
            }
        }

        // Mostrar lista de notificaciones
        function mostrarNotificaciones(notificaciones) {
            const lista = document.getElementById('lista-notificaciones');

            if (notificaciones.length === 0) {
                lista.innerHTML = `
                    <li class="notification-empty text-center">
                        <div class="d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                            <div>¡No tienes observaciones pendientes!</div>
                        </div>
                    </li>
                `;
                return;
            }

            let html = '';
            notificaciones.forEach(notif => {
                html += `
                    <li class="notification-item" onclick="verObservacion(${notif.planificacion_id})">
                        <div class="notification-title">
                            ${notif.asignatura} - ${notif.unidad}
                        </div>
                        <div class="notification-content">
                            ${notif.observacion.substring(0, 80)}${notif.observacion.length > 80 ? '...' : ''}
                        </div>
                        <div class="notification-time">
                            <i class="fas fa-clock me-1"></i>
                            ${formatearTiempo(notif.fecha_observacion)}
                        </div>
                    </li>
                `;
            });

            lista.innerHTML = html;
        }

        // Formatear tiempo relativo
        function formatearTiempo(fecha) {
            const ahora = new Date();
            const fechaObservacion = new Date(fecha);
            const diferencia = ahora - fechaObservacion;

            const minutos = Math.floor(diferencia / (1000 * 60));
            const horas = Math.floor(diferencia / (1000 * 60 * 60));
            const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));

            if (minutos < 60) {
                return `Hace ${minutos} min`;
            } else if (horas < 24) {
                return `Hace ${horas}h`;
            } else {
                return `Hace ${dias}d`;
            }
        }

        // Ver observación específica
        function verObservacion(planificacionId) {
            // Redirigir a la página de planificaciones con filtro
            document.getElementById('mainFrame').src = `../planificaciones.php?highlight_planificacion=${planificacionId}`;
        }

        // Marcar todas como leídas
        async function marcarTodasLeidas() {
            try {
                const response = await fetch('../../app/Notificaciones/NotificacionesService.php?action=marcarLeidas', {
                    method: 'POST'
                });
                const data = await response.json();

                if (data.success) {
                    actualizarBadge(0);
                    mostrarNotificaciones([]);
                }
            } catch (error) {
                console.error('Error al marcar como leídas:', error);
            }
        }
    </script>


</body>

</html>