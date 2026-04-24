<?php
/**
 * overview_panel.php
 * 
 * Panel de resumen general para el dashboard. Muestra estadísticas clave y accesos rápidos.   
 * 
 * @author     Jaime A. Muñoz Rodriguez
 * @version    1.1
 */
$count_properties   = $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$count_available    = $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'available'")->fetchColumn();
$count_users        = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$count_quotes       = $pdo->query("SELECT COUNT(*) FROM quote_requests")->fetchColumn();
$count_pending      = $pdo->query("SELECT COUNT(*) FROM quote_requests WHERE status = 'new'")->fetchColumn();
?>

<h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
<p style="color:#666; margin-bottom:30px;">Resumen general del sistema.</p>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:20px; margin-bottom:30px;">

    <div class="card" style="border-left:4px solid #1e3a8a; cursor:default;">
        <h3 style="color:#666; font-size:13px; text-transform:uppercase; margin:0 0 8px;">Propiedades</h3>
        <p style="font-size:2rem; font-weight:bold; color:#1e3a8a; margin:0;"><?= $count_properties ?></p>
        <small style="color:#888;"><?= $count_available ?> disponibles</small>
    </div>

    <div class="card" style="border-left:4px solid #16a34a; cursor:default;">
        <h3 style="color:#666; font-size:13px; text-transform:uppercase; margin:0 0 8px;">Usuarios</h3>
        <p style="font-size:2rem; font-weight:bold; color:#16a34a; margin:0;"><?= $count_users ?></p>
        <small style="color:#888;">registrados</small>
    </div>

    <div class="card" style="border-left:4px solid #d97706; cursor:default;">
        <h3 style="color:#666; font-size:13px; text-transform:uppercase; margin:0 0 8px;">Solicitudes</h3>
        <p style="font-size:2rem; font-weight:bold; color:#d97706; margin:0;"><?= $count_quotes ?></p>
        <small style="color:#888;"><?= $count_pending ?> nuevas</small>
    </div>
</div>

<div class="card" style="cursor:default;">
    <h3 style="margin-top:0;">Accesos rápidos</h3>
    <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <a href="dashboard.php?section=properties&action=add"
           style="background:#1e3a8a; color:white; padding:10px 18px; border-radius:6px; text-decoration:none; font-size:14px;">
            + Nueva Propiedad
        </a>
        <a href="dashboard.php?section=quotes"
           style="background:#d97706; color:white; padding:10px 18px; border-radius:6px; text-decoration:none; font-size:14px;">
            Ver Solicitudes
        </a>
        <a href="db_test.php"
           style="background:#475569; color:white; padding:10px 18px; border-radius:6px; text-decoration:none; font-size:14px;">
            DB Test Panel
        </a>
    </div>
</div>
