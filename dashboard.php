<?php
/**
 * dashboard.php
 * Panel de administración principal para MoveAndGoPR. 
 * Permite a los administradores ver estadísticas clave, gestionar propiedades, etc
 * 
 * 
 * @author     Christian
 * @author     Jaime A. Muñoz Rodriguez
 * @version    1.2
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}


$section = $_GET['section'] ?? 'overview';
$allowed_sections = ['overview', 'properties', 'quotes', 'users'];
if (!in_array($section, $allowed_sections)) {
    $section = 'overview';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MoveAndGoPR</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .sidebar a.active {
            color: #ffffff;
            background: #334155;
            border-radius: 5px;
            padding: 5px 8px;
            margin-left: -8px;
        }
        .sidebar a {
            padding: 5px 8px;
            margin-left: -8px;
            border-radius: 5px;
            transition: background 0.2s;
        }
        .sidebar a:hover {
            background: #2d3f55;
        }
        .sidebar .nav-section-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin: 18px 0 6px 0;
        }
        .sidebar .nav-divider {
            border: none;
            border-top: 1px solid #334155;
            margin: 12px 0;
        }
    </style>
</head>
<body>

<div class="admin-container">

    <div class="sidebar">
        <div class="sidebar-nav">
            <h2>Admin Panel</h2>

            <p class="nav-section-label">General</p>
            <a href="dashboard.php?section=overview"
               class="<?php echo $section === 'overview' ? 'active' : '' ?>">
                Dashboard
            </a>

            <p class="nav-section-label">Gestión</p>
            <a href="dashboard.php?section=properties"
               class="<?php echo $section === 'properties' ? 'active' : '' ?>">
                Propiedades
            </a>
            <a href="dashboard.php?section=quotes"
               class="<?php echo $section === 'quotes' ? 'active' : '' ?>">
                Cotizaciones
            </a>
            <a href="dashboard.php?section=users"
               class="<?php echo $section === 'users' ? 'active' : '' ?>">
                Usuarios
            </a>

            <hr class="nav-divider">
            <a href="index.php">← Volver al sitio</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>

        <div class="sidebar-footer">
            <span>Move & Go PR</span>
            <span>© <?= date('Y') ?></span>
        </div>
    </div>

    <div class="main-content">
        <?php
        switch ($section) {
            case 'properties':
                include 'includes/properties_panel.php';
                break;
            case 'quotes':
                include 'includes/quotes_panel.php';
                break;
            case 'users':
                echo '<h1>Usuarios</h1><p style="color:#888;">Esta sección está en desarrollo.</p>';
                break;
            default:
                include 'includes/overview_panel.php';
                break;
        }
        ?>
    </div>

</div>

</body>
</html>
