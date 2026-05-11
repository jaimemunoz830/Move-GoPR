<?php
/**
 * login.php
 * Pagina para iniciar sesion. Verifica el email y contraseña contra la base de datos, y establece la sesion del usuario.
 * Si el inicio de sesion es exitoso, redirige a dashboard.php. Si no, muestra un mensaje de error.
 *  
 * @author Jaime A. Muñoz Rodriguez
 * @version 2.0
 */
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE){
    session_start();
} 
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if (($user['status'] ?? 'active') === 'pending') {
                $error = "Tu cuenta está pendiente de aprobación por el administrador.";
            } elseif (($user['status'] ?? 'active') === 'rejected') {
                $error = "Tu solicitud de registro fue rechazada. Contacta al administrador.";
            } else {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role']      = $user['role'];
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $error = "Email o contraseña incorrectos.";
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Move & Go PR</title>
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
        <h2>Iniciar Sesión</h2>
        <p class="auth-subtitle">Accede a tu cuenta para continuar.</p>

        <?php if ($error): ?>
            <div class="auth-alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="auth-field">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="tucorreo@ejemplo.com" required>
            </div>

            <div class="auth-field">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" required>
            </div>

            <button type="submit" class="auth-btn">Entrar</button>
        </form>

        <p class="auth-link">¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
        <a class="back-link" href="index.php">← Volver al sitio</a>
    </div>

</div>

</body>
</html>
