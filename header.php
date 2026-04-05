<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoveAndGoPR</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .main-header {
            background: #1e3a8a;
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo h1 {
            color: white;
            margin: 0;
            font-size: 1.5rem;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .nav-links a:hover {
            color: #80ff00; 
        }

        .btn-login {
            background: #80ff00;
            color: #1e3a8a !important;
            padding: 8px 20px;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn-login:hover {
            background: white !important;
        }

        .btn-admin-tool {
            border: 1px solid #80ff00;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px !important;
        }
    </style>
</head>
<body>

<header class="main-header">
    <div class="logo">
        <h1>MoveAnd<span class="green">GoPR</span></h1>
    </div>
    
    <nav class="nav-links">
        <a href="index.php">Inicio</a>
        <a href="moving.php">Mudanza</a>
        <a href="map.php">Propiedades</a>
        
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="dashboard.php" class="btn-admin-tool">Dashboard</a>
            <a href="db_test.php" class="btn-admin-tool">DB Test</a>
            <a href="logout.php" class="btn-login">Cerrar Sesión</a>
        <?php elseif(isset($_SESSION['user_id'])): ?>
            <a href="logout.php" class="btn-login">Cerrar Sesión</a>
        <?php else: ?>
            <a href="login.php" class="btn-login">Iniciar Sesión</a>
        <?php endif; ?>
    </nav>
</header>