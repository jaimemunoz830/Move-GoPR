<?php
include 'header.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
             $_SESSION['user_id'] = $user['id'];
             $_SESSION['user_name'] = $user['name'];
             $_SESSION['role'] = $user['role'];
             header("Location: index.php");
             exit();
        } else {
            $error = "Email o contraseña incorrectos.";
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>

<main class="login-container" style="padding: 100px 20px; min-height: 80vh; display: flex; justify-content: center; align-items: center; background: #f4f7f6;">
    <div class="card" style="max-width: 400px; width: 100%; padding: 30px; cursor: default;">
        <h2 style="text-align: center; color: #1e3a8a; margin-bottom: 20px;">Iniciar Sesión</h2>
        
        <?php if($error): ?>
            <p style="color: #dc2626; background: #fef2f2; padding: 10px; border-radius: 5px; font-size: 14px; text-align: center;">
                <?php echo $error; ?>
            </p>
        <?php endif; ?>

        <form action="login.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Correo Electrónico</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Contraseña</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>

            <button type="submit" class="btn blue" style="width: 100%; border: none; cursor: pointer;">Entrar</button>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>




