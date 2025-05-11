<?php
session_start();
session_unset();  // Elimina todas las variables de sesión
session_destroy(); // Destruye la sesión

// Redirige al login después de cerrar sesión
header("Location: index.php");
exit();
?>
