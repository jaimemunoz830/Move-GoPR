<?php
/**
 * register.php
 * Pagina para registar nuevos usuarios. El styling es practicamente el mismo que el de login.php asi que no esta completado.
 * Todos los usuarios registrados a través de esta página tendrán el rol de "admin".
 * 
 * El proposito de esta pagina es para que el resto del grupo pueda crear sus propias cuentas y accesar la pagina.
 * No verifica permisos porque se asume que solo los administradores tendrán acceso a esta página, y es temporera.
 * @author Jaime A. Muñoz Rodriguez
 * @version 1.0
 */

include 'header.php';

$error = "";

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
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
            $stmt->execute([$name, $email, $hash]);
            $newId = $pdo->lastInsertId();
            
            $_SESSION['user_id']   = $newId;
            $_SESSION['user_name'] = $name;
            $_SESSION['role']      = 'admin';
            header("Location: index.php");
            exit();
        }
    }
}
?>

<main style="padding: 80px 20px; min-height: 80vh; display: flex; justify-content: center; align-items: center; background: #f4f7f6;">
    <div class="card" style="max-width: 420px; width: 100%; padding: 30px; cursor: default;">
        <h2 style="text-align: center; color: #1e3a8a; margin-bottom: 20px;">Registrar Miembro</h2>

        <?php if ($error): ?>
            <p style="color: #dc2626; background: #fef2f2; padding: 10px; border-radius: 5px; font-size: 14px; text-align: center; margin-bottom: 15px;">
                <?php echo $error; ?>
            </p>
        <?php endif; ?>

<form method="POST" action="register.php" style="display: flex; flex-direction: column; gap: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Nombre completo</label>
                <input type="text" name="name" required
                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Correo Electrónico</label>
                <input type="email" name="email" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Contraseña</label>
                <input type="password" name="password" required minlength="8"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Confirmar Contraseña</label>
                <input type="password" name="confirm_password" required minlength="8"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
            </div>

            <button type="submit" class="btn blue" style="width: 100%; border: none; cursor: pointer; margin-top: 5px;">
                Crear Cuenta
            </button>
        </form>

    </div>
</main>

<?php include 'footer.php'; ?>
