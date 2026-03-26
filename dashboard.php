<?php
session_start();

//Solamente administradores permitidos
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="admin-container">
    
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="#">Manage Services</a>
        <a href="#">Manage Properties</a>
        <a href="#">Manage Users</a>
        <a href="index.php">Back to Website</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
        <p>Aqui se maneja y controla la pagina.</p>

        <div class="card">
            <h3>Total Services</h3>
            <p>0</p>
        </div>

        <div class="card">
            <h3>Total Properties</h3>
            <p>0</p>
        </div>

        <div class="card">
            <h3>Total Users</h3>
            <p>0</p>
        </div>
    </div>

</div>

</body>
</html>