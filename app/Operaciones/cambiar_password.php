<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Configurar el tipo de contenido de respuesta
header('Content-Type: application/json');

try {
    // Obtener los datos JSON del cuerpo de la petición
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Datos no válidos');
    }

    $passwordActual = trim($input['passwordActual'] ?? '');
    $passwordNueva = trim($input['passwordNueva'] ?? '');
    $codigoUsuario = $_SESSION['usuario']['codigo'];

    // Validaciones básicas
    if (empty($passwordActual) || empty($passwordNueva)) {
        throw new Exception('Todos los campos son obligatorios');
    }

    if (strlen($passwordNueva) < 6) {
        throw new Exception('La nueva contraseña debe tener al menos 6 caracteres');
    }

    if ($passwordActual === $passwordNueva) {
        throw new Exception('La nueva contraseña debe ser diferente a la actual');
    }

    // Verificar la contraseña actual
    $stmt = $pdo->prepare("SELECT password, fecha_ingreso FROM docente WHERE codigo = ?");
    $stmt->execute([$codigoUsuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }

    // Comparar contraseña actual (sin hash, como se maneja en tu sistema)
    if ($usuario['password'] !== $passwordActual) {
        throw new Exception('La contraseña actual es incorrecta');
    }

    // Actualizar contraseña y fecha_ingreso
    $fechaActual = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("UPDATE docente SET password = ?, fecha_ingreso = ? WHERE codigo = ?");
    $result = $stmt->execute([$passwordNueva, $fechaActual, $codigoUsuario]);

    if (!$result) {
        throw new Exception('Error al actualizar la contraseña');
    }

    // Actualizar la sesión para reflejar que ya cambió la contraseña
    $_SESSION['usuario']['fecha_ingreso'] = $fechaActual;

    echo json_encode([
        'success' => true, 
        'message' => 'Contraseña actualizada correctamente'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error en la base de datos'
    ]);
}
?>