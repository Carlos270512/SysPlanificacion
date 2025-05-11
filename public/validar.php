<?php
session_start();
require_once '../config/conexion.php'; // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar entrada
    $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
    $password = $_POST['contraseña'] ?? '';

    if (!$correo || empty($password)) {
        header('Location: index.php?error=datos_invalidos');
        exit();
    }

    // Verificar conexión a la base de datos
    if (!$pdo) {
        error_log('Error: No se pudo conectar a la base de datos.');
        header('Location: index.php?error=conexion_fallida');
        exit();
    }

    try {
        // Consultar usuario con correo y contraseña
        $query = "SELECT * FROM docente WHERE correo = :correo AND password = :password";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Iniciar sesión y redirigir según el rol
            $_SESSION['usuario'] = $usuario;

            switch ($usuario['rol']) {
                case 'ADMIN':
                    header('Location: admin_dashboard.php');
                    break;
                case 'COORDINADOR':
                    header('Location: coordinador_dashboard.php');
                    break;
                case 'DOCENTE':
                    header('Location: docente_dashboard.php');
                    break;
                default:
                    session_destroy();
                    header('Location: index.php?error=rol_no_valido');
            }
        } else {
            // Credenciales inválidas
            error_log('Error: Credenciales inválidas para el usuario con correo ' . $correo);
            header('Location: index.php?error=credenciales_invalidas');
        }
    } catch (PDOException $e) {
        // Manejo de errores de base de datos
        error_log('Error en la base de datos: ' . $e->getMessage());
        header('Location: index.php?error=error_servidor');
    }
} else {
    // Si no es una solicitud POST, redirigir al inicio
    header('Location: index.php');
}
exit();