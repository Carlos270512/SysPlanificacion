<?php
session_start();
$mensaje = '';
if (isset($_GET['error'])) {
    $mensaje = '<div class="alert alert-danger text-center">Correo o contraseña incorrectos.</div>';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Tu CSS personalizado -->
    <link href="assets/css/indexstyle.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="bg-login">
        <div class="login-card">
            <h2 class="text-center mb-4">Iniciar sesión</h2>
            <img src="assets/img/logotvn.png" alt="Logo Vida Nueva" class="logo-efecto">
            <?php echo $mensaje; ?>
            <form action="validar.php" method="POST" autocomplete="off">
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="Ingrese su correo" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="contraseña" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="contraseña" name="contraseña" placeholder="Ingrese su contraseña" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-3">Iniciar sesión</button>
            </form>
        </div>
    </div>
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>