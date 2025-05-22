<?php
$unidad_id = isset($_GET['unidad_id']) ? $_GET['unidad_id'] : '';
$unidad_nombre = isset($_GET['unidad_nombre']) ? $_GET['unidad_nombre'] : '';
?>
<div class="alert alert-info">
    <strong>ID Unidad:</strong> <?php echo htmlspecialchars($unidad_id); ?><br>
    <strong>Nombre Unidad:</strong> <?php echo htmlspecialchars($unidad_nombre); ?>
</div>
<!-- Aquí puedes agregar el formulario para ingresar semanas -->