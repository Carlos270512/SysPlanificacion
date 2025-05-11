<?php
// Asegúrate de que este archivo está en la carpeta public/
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Importación de Bootstrap sin atributo integrity -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Importación de tu propio CSS -->
    <link href="assets/css/indexstyle.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h2 class="text-center my-5">Iniciar sesión</h2>

        <!-- Formulario de login -->
        <form action="validar.php" method="POST">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input type="email" class="form-control" id="correo" name="correo" required>
            </div>
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contraseña" name="contraseña" required>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
            </div>
        </form>
    </div>

    <!-- Enlace al favicon (asegúrate de que el archivo esté en la carpeta correcta) -->
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">

    <!-- Archivos JavaScript de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>