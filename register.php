<?php
/**
 * register.php
 * Los registros se crean con role='customer' y status='pending'.
 * El administrador aprueba o rechaza desde el dashboard.
 *
 * @author Jaime A. Muñoz Rodriguez
 * @version 2.0
 */
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE){
    session_start();
} 

$error      = "";
$registered = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = "Por favor, completa todos los campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } elseif (strlen($password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres.";
    } elseif ($password !== $confirm) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Ya existe una cuenta con ese correo electrónico.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'customer', 'pending')");
            $stmt->execute([$name, $email, $hash]);
            $registered = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta — Move & Go PR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>

<div class="auth-card">

    <div class="auth-brand">
        <img src="img/moveandgopr-logo-solito.png" alt="Move & Go PR">
        <div>
            <h3>Move & Go PR</h3>
            <p>Tu hogar, tu mudanza,<br>tu tranquilidad.</p>
        </div>
    </div>

    <div class="auth-form-wrap">
        <?php if ($registered): ?>

            <div class="auth-success">
                <div class="auth-success-icon">✅</div>
                <h3>Registro recibido</h3>
                <p>Tu solicitud está siendo procesada. El administrador revisará tu cuenta y te dará acceso en breve.</p>
                <a href="index.php">Volver al inicio</a>
            </div>

        <?php else: ?>

            <h2>Crear Cuenta</h2>
            <p class="auth-subtitle">Completa el formulario para solicitar acceso.</p>

            <?php if ($error): ?>
                <div class="auth-alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="auth-field">
                    <label for="name">Nombre completo</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           placeholder="Tu nombre completo" required>
                </div>

                <div class="auth-field">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="tucorreo@ejemplo.com" required>
                </div>

                <div class="auth-field">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password"
                           placeholder="Mínimo 8 caracteres" required minlength="8">
                    <span class="auth-hint">Mínimo 8 caracteres.</span>
                </div>

                <div class="auth-field">
                    <label for="confirm_password">Confirmar contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Repite tu contraseña" required minlength="8">
                </div>

                <button type="submit" class="auth-btn">Crear Cuenta</button>
            </form>

            <p class="auth-link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
            <a class="back-link" href="index.php">← Volver al sitio</a>

        <?php endif; ?>
    </div>

</div>

</body>
</html>
