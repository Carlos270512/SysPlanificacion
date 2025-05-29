<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/../config/conexion.php';

$codigo = $_GET['codigo'] ?? '';
if (!$codigo) {
    echo "Código de asignatura no proporcionado.";
    exit();
}

// Consulta la asignatura y el docente
$stmt = $pdo->prepare("SELECT a.*, d.nombre as docente_nombre FROM asignatura a
    LEFT JOIN docente d ON a.docente_codigo = d.codigo
    WHERE a.codigo = ?");
$stmt->execute([$codigo]);
$asig = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asig) {
    echo "Asignatura no encontrada.";
    exit();
}

// Lógica de modalidad según jornada
$jornada = strtoupper($asig['jornada']);
switch ($jornada) {
    case 'PM':
    case 'PN':
        $modalidad = 'PRESENCIAL';
        break;
    case 'HM':
    case 'HN':
        $modalidad = 'HIBRIDA';
        break;
    case 'V':
        $modalidad = 'VIRTUAL';
        break;
    case 'S':
        $modalidad = 'SABADOS';
        break;
    default:
        $modalidad = 'NO DEFINIDA';
        break;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Planificación de Clase</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        .tabla-planificacion td,
        .tabla-planificacion th {
            border: 1px solid #000;
            padding: 4px 8px;
        }

        .tabla-planificacion th {
            background: #ffff99;
            color: #000;
        }

        .tabla-planificacion {
            width: 90%;
            margin: 0 auto;
            border-collapse: collapse;
        }

        .resaltado {
            background: #ffff99;
            font-weight: bold;
        }

        .subrayado {
            border-bottom: 2px solid #888;
            display: inline-block;
            min-width: 80px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <table class="tabla-planificacion">
            <tr>
                <th colspan="6" style="text-align:center;">PLANIFICACION DE CLASE</th>
            </tr>
            <tr>
                <td class="resaltado">Asignatura:</td>
                <td><?php echo htmlspecialchars($asig['nombre_asignatura']); ?></td>
                <td class="resaltado">Código de la asignatura:</td>
                <td><?php echo htmlspecialchars($asig['codigo']); ?></td>
                <td class="resaltado">Modalidad:</td>
                <td><?php echo $modalidad; ?></td>
            </tr>
            <tr>
                <td class="resaltado">Nivel:</td>
                <td><?php echo htmlspecialchars($asig['nivel']); ?></td>
                <td class="resaltado">Jornada:</td>
                <td><?php echo htmlspecialchars($asig['jornada']); ?></td>
                <td class="resaltado">Docente:</td>
                <td><?php echo htmlspecialchars($asig['docente_nombre']); ?></td>
            </tr>
        </table>
        <br>
        <table class="tabla-planificacion">
            <tr>
                <td colspan="6" style="text-align:left;">
                    <span class="resaltado">Unidad N°</span>
                    <span class="subrayado">&nbsp;&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;&nbsp;</span>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <span class="resaltado">Nombre:</span>
                    <span class="subrayado" style="min-width:200px;">&nbsp;</span>
                </td>
            </tr>
        </table>
    </div>

    <button type="button" class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#modalConfirmarAtras">
        &larr; Atrás
    </button>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="modalConfirmarAtras" tabindex="-1" aria-labelledby="modalConfirmarAtrasLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmarAtrasLabel">¿Está seguro de regresar?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    Si regresa perderá los cambios realizados en la planificación.<br>¿Desea continuar?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="planificaciones.php" class="btn btn-danger">Sí, regresar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS para el modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>