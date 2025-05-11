<?php
// config/conexion.php

$host = 'localhost';           // O la IP de tu servidor de base de datos
$dbname = 'sisplanificacion';  // Nombre de la base de datos
$username = 'root';            // Tu usuario de MySQL (ajústalo según tu configuración)
$password = '';                // Tu contraseña de MySQL (ajústalo según tu configuración)

// Crear conexión con la base de datos
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Establecer el modo de error de PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Establecer la codificación de caracteres para evitar problemas con caracteres especiales
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    // Si ocurre un error en la conexión, muestra un mensaje de error
    die("Error de conexión: " . $e->getMessage());
}

// Retorna la conexión para usarla en otros archivos
return $pdo;
?>
