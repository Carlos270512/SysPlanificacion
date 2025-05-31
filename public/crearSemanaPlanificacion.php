<?php
$id_unidad = isset($_GET['id_unidad']) ? intval($_GET['id_unidad']) : null;
$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Semana Planificación</title>
</head>

<body>
    <h1>Hola soy semanassss</h1>
    <p>ID de la unidad recibida: <strong><?php echo htmlspecialchars($id_unidad); ?></strong></p>
    <!-- Botón Atrás -->
    <a href="crearPlanificaciones.php?codigo=<?php echo urlencode($codigo); ?>&volver=1&id_unidad=<?php echo urlencode($id_unidad); ?>" class="btn btn-secondary">
        &larr; Atrás
    </a>
</body>

</html>