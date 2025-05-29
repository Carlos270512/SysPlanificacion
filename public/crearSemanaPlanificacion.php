<?php
$id_unidad = isset($_GET['id_unidad']) ? intval($_GET['id_unidad']) : null;
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
</body>
</html>