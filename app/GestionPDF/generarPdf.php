<?php
// Recibe los datos por GET
$semana_id = isset($_GET['semana_id']) ? htmlspecialchars($_GET['semana_id']) : '';
$unidad_nombre = isset($_GET['unidad_nombre']) ? htmlspecialchars($_GET['unidad_nombre']) : '';
$semana_inicio = isset($_GET['semana_inicio']) ? htmlspecialchars($_GET['semana_inicio']) : '';
$semana_fin = isset($_GET['semana_fin']) ? htmlspecialchars($_GET['semana_fin']) : '';
?>
<div class="container mt-3">
    <h4>Datos recibidos para generar PDF</h4>
    <ul class="list-group">
        <li class="list-group-item"><strong>ID Semana:</strong> <?php echo $semana_id; ?></li>
        <li class="list-group-item"><strong>Nombre de la Unidad:</strong> <?php echo $unidad_nombre; ?></li>
        <li class="list-group-item"><strong>Fecha de Inicio:</strong> <?php echo $semana_inicio; ?></li>
        <li class="list-group-item"><strong>Fecha de Fin:</strong> <?php echo $semana_fin; ?></li>
    </ul>
    <div class="alert alert-info mt-3">
        Aquí puedes mostrar el PDF o más información según lo que necesites.
    </div>
</div>