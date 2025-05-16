<?php
$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Reportes</title>
</head>
<body>
    <h2>Datos recibidos:</h2>
    <p><strong>Código:</strong> <?= htmlspecialchars($codigo) ?></p>
    <p><strong>Nombre asignatura:</strong> <?= htmlspecialchars($nombre) ?></p>
</body>
</html>