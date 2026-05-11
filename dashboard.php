<?php
ob_start();
/**
 * dashboard.php
 * Panel de administración principal para MoveAndGoPR. 
 * Permite a los administradores ver estadísticas clave, gestionar propiedades, etc
 * 
 * 
 * @author     Christian J. Lespier Vals
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

$msg = $_GET['msg'] ?? '';

$pending_user_count = 0;
try {
    $pending_user_count = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
} catch (Exception $e) {
    //????
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MoveAndGoPR</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
    function showToast(message, type) {
        type = type || 'success';
        var container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        var toast = document.createElement('div');
        toast.className = 'toast' + (type !== 'success' ? ' ' + type : '');
        toast.innerHTML = '<span>' + message + '</span><button onclick="this.parentElement.remove()" style="background:none;border:none;color:rgba(255,255,255,0.6);font-size:18px;cursor:pointer;padding:0;margin-left:14px;line-height:1;">&times;</button>';
        container.appendChild(toast);
        setTimeout(function() {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(function() { toast.remove(); }, 300);
        }, 10000);
    }
    </script>
    <style>
        .sidebar a.active {
            color: #ffffff;
            background: rgba(255,255,255,0.2);
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
            background: rgba(255,255,255,0.12);
        }
        .sidebar .nav-section-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.55);
            margin: 18px 0 6px 0;
        }
        .sidebar .nav-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.2);
            margin: 12px 0;
        }
    </style>
</head>
<body>

<header class="mobile-header">
    <button class="hamburger-btn" id="sidebarToggle" aria-label="Abrir menú">&#9776;</button>
    <span class="mobile-header-title">Admin Panel</span>
</header>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-container">

    <div class="sidebar">
        <div class="sidebar-nav">
            <img src="img/moveandgopr-logo-solito.png" alt="Move & Go PR" class="sidebar-logo">
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
                Solicitudes
            </a>
            <a href="dashboard.php?section=users"
               class="<?php echo $section === 'users' ? 'active' : '' ?>"
               style="display:flex; align-items:center; justify-content:space-between;">
                Usuarios
                <?php if ($pending_user_count > 0): ?>
                    <span style="background:#dc2626; color:white; border-radius:10px; padding:1px 7px; font-size:11px; font-weight:700; flex-shrink:0;">
                        <?= $pending_user_count ?>
                    </span>
                <?php endif; ?>
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
                include 'includes/users_panel.php';
                break;
            default:
                include 'includes/overview_panel.php';
                break;
        }

        $toast_map = [
            'created'        => ['Propiedad añadida exitosamente.', 'success'],
            'updated'        => ['Cambios guardados.', 'success'],
            'deleted'        => ['Eliminado correctamente.', 'success'],
            'approved'       => ['Usuario aprobado y activado.', 'success'],
            'rejected'       => ['Solicitud rechazada.', 'success'],
            'selfdelete'     => ['No puedes eliminar tu propia cuenta.', 'error'],
            'notfound'       => ['Elemento no encontrado.', 'error'],
            'notes_saved'    => ['Notas guardadas.', 'success'],
            'quote_deleted'  => ['Cotización eliminada.', 'success'],
            'status_updated' => ['Estado actualizado.', 'success'],
        ];
        if (isset($toast_map[$msg])):
            [$toast_text, $toast_type] = $toast_map[$msg];
        ?>
        <script>showToast(<?= json_encode($toast_text) ?>, <?= json_encode($toast_type) ?>);</script>
        <?php endif; ?>

    </div>

</div>

<script>
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('open');
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
    });
</script>
</body>
</html>
