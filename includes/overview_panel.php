<?php
/**
 * 
 * overview_panel.php
 * Panel de resumen para el dashboard administrativo, mostrando estadísticas clave y las solicitudes más recientes.
 * 
 * @author     Christian J. Lespier Vals
 * @author     Jaime A. Muñoz Rodriguez
 * @version    2.0
 */

$count_properties    = (int)$pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$count_available     = (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'available'")->fetchColumn();
$count_sale          = (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE type = 'sale'")->fetchColumn();
$count_rent          = (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE type = 'rent'")->fetchColumn();
$count_users         = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$count_pending_users = 0;
try {
    $count_pending_users = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
} catch (Exception $e) {}

$quote_counts = $pdo->query(
    "SELECT status, COUNT(*) AS total FROM quote_requests GROUP BY status"
)->fetchAll(PDO::FETCH_KEY_PAIR);

$count_quotes     = array_sum($quote_counts);
$count_new        = (int)($quote_counts['new']       ?? 0);
$count_contacted  = (int)($quote_counts['contacted'] ?? 0);
$count_closed     = (int)($quote_counts['closed']    ?? 0);

$recent_quotes = $pdo->query(
    "SELECT * FROM quote_requests ORDER BY created_at DESC LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card" style="margin-bottom:24px; background:linear-gradient(135deg,#f0f7eb 0%,#ffffff 60%); border-left:4px solid #4E8F22; cursor:default;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
        <div>
            <p style="margin:0 0 4px; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:1px; color:#94a3b8;">Panel de administración</p>
            <h1 style="margin:0; font-size:1.6rem; color:#1a1a1a;">Bienvenido, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
        </div>
        <div style="text-align:right;">
            <div id="overview-date" style="font-size:16px; color:#374151; font-weight:600;"></div>
            <div id="overview-time" style="font-size:36px; font-weight:700; color:#4E8F22; line-height:1.1; letter-spacing:-1px;"></div>
            <style>
                @media (max-width: 580px) {
                    #overview-date, #overview-time { display: none !important; }
                }
            </style>
        </div>
    </div>
</div>

<script>
(function() {
    const dias  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    function update() {
        const now = new Date();
        let h = now.getHours();
        const m  = String(now.getMinutes()).padStart(2,'0');
        const s  = String(now.getSeconds()).padStart(2,'0');
        const ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        document.getElementById('overview-date').textContent =
            `Hoy es ${dias[now.getDay()]}, ${now.getDate()} de ${meses[now.getMonth()]} de ${now.getFullYear()}`;
        document.getElementById('overview-time').textContent = `${h}:${m}:${s} ${ap}`;
    }
    update();
    setInterval(update, 1000);
})();
</script>

<!-- Stats strip -->
<?php
$pct_new       = $count_quotes ? round($count_new       / $count_quotes * 100) : 0;
$pct_contacted = $count_quotes ? round($count_contacted / $count_quotes * 100) : 0;
$pct_closed    = $count_quotes ? 100 - $pct_new - $pct_contacted              : 0;
?>
<div class="card stats-strip" style="margin-bottom:24px;">

    <!-- Propiedades -->
    <div class="stat-block">
        <div class="stat-block-label">
            <i class="fa-solid fa-building"></i>
            <span>Propiedades</span>
        </div>
        <div class="stat-block-content">
            <span class="stat-big-num" style="color:#4E8F22;"><?= $count_properties ?></span>
            <div class="stat-detail">
                <span><?= $count_available ?> disponibles</span>
                <span style="display:flex; gap:10px; flex-wrap:wrap;">
                    <span style="color:#1e3a8a; font-weight:600;">Venta: <?= $count_sale ?></span>
                    <span style="color:#0891b2; font-weight:600;">Alquiler: <?= $count_rent ?></span>
                </span>
            </div>
        </div>
    </div>

    <div class="stat-divider"></div>

    <!-- Usuarios -->
    <a href="dashboard.php?section=users" class="stat-block stat-block-link">
        <div class="stat-block-label">
            <i class="fa-solid fa-users"></i>
            <span>Usuarios</span>
        </div>
        <div class="stat-block-content">
            <span class="stat-big-num" style="color:#16a34a;"><?= $count_users ?></span>
            <div class="stat-detail">
                <span><?= $count_users ?> registrados</span>
                <?php if ($count_pending_users > 0): ?>
                    <span style="color:#d97706; font-weight:600;"><?= $count_pending_users ?> pendiente<?= $count_pending_users > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </div>
        </div>
    </a>

    <div class="stat-divider"></div>

    <!-- Solicitudes -->
    <a href="dashboard.php?section=quotes" class="stat-block stat-block-link">
        <div class="stat-block-label">
            <i class="fa-solid fa-inbox"></i>
            <span>Solicitudes</span>
        </div>
        <div class="stat-block-content">
            <span class="stat-big-num" style="color:#d97706;"><?= $count_quotes ?></span>
            <div class="stat-detail">
                <?php if ($count_quotes > 0): ?>
                <div class="stat-seg-bar">
                    <?php if ($pct_new > 0):       ?><div style="background:#d97706; width:<?= $pct_new ?>%;"></div><?php endif; ?>
                    <?php if ($pct_contacted > 0): ?><div style="background:#1e3a8a; width:<?= $pct_contacted ?>%;"></div><?php endif; ?>
                    <?php if ($pct_closed > 0):    ?><div style="background:#16a34a; width:<?= $pct_closed ?>%;"></div><?php endif; ?>
                </div>
                <div class="stat-seg-labels">
                    <span style="color:#d97706;"><?= $count_new ?> nuevas</span>
                    <span style="color:#1e3a8a;"><?= $count_contacted ?> contactadas</span>
                    <span style="color:#16a34a;"><?= $count_closed ?> cerradas</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </a>

</div>


<!-- Solicitudes recientes -->
<?php if (!empty($recent_quotes)): ?>
<div class="card" style="cursor:default;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h3 style="margin:0; font-size:14px; color:#374151;">Solicitudes recientes</h3>
        <a href="dashboard.php?section=quotes" style="font-size:13px; color:#4E8F22; text-decoration:none; font-weight:600;">Ver todas</a>
    </div>

    <?php
    $status_colors = ['new' => '#d97706', 'contacted' => '#1e3a8a', 'closed' => '#16a34a'];
    $status_labels = ['new' => 'Nueva', 'contacted' => 'Contactado', 'closed' => 'Cerrado'];
    $tipo_colors   = ['mudanza' => '#0891b2', 'mejoras' => '#7c3aed'];
    $tipo_labels   = ['mudanza' => 'Mudanza', 'mejoras' => 'Mejoras'];
    foreach ($recent_quotes as $rq):
        $sc = $status_colors[$rq['status']] ?? '#94a3b8';
        $sl = $status_labels[$rq['status']] ?? $rq['status'];
        $tc = $tipo_colors[$rq['tipo_servicio']] ?? '#94a3b8';
        $tl = $tipo_labels[$rq['tipo_servicio']] ?? $rq['tipo_servicio'];
    ?>
    <a href="dashboard.php?section=quotes&action=view&id=<?= $rq['id'] ?>"
       style="display:flex; align-items:center; gap:14px; padding:11px 0; border-bottom:1px solid #f1f5f9; text-decoration:none; color:inherit;">
        <div style="width:4px; height:40px; border-radius:2px; background:<?= $sc ?>; flex-shrink:0;"></div>
        <div style="flex:1; min-width:0;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:3px;">
                <strong style="font-size:14px; color:#1e293b;"><?= htmlspecialchars($rq['nombre']) ?></strong>
                <span style="background:<?= $tc ?>; color:white; padding:1px 7px; border-radius:8px; font-size:11px;"><?= $tl ?></span>
            </div>
            <span style="font-size:13px; color:#64748b;">
                <?= htmlspecialchars($rq['servicio'] ?: '—') ?>
                <?php if ($rq['ubicacion']): ?>
                    &nbsp;·&nbsp;<?= htmlspecialchars($rq['ubicacion']) ?>
                <?php endif; ?>
            </span>
        </div>
        <div style="text-align:right; flex-shrink:0; display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
            <span style="color:<?= $sc ?>; font-size:12px; font-weight:700; white-space:nowrap;"><?= $sl ?></span>
            <?php
                $meses_ov = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
                $dt = new DateTime($rq['created_at']);
                echo '<span style="font-size:12px; color:#94a3b8; white-space:nowrap;">' . $dt->format('j') . ' de ' . $meses_ov[(int)$dt->format('n') - 1] . ' de ' . $dt->format('Y') . '</span>';
            ?>
        </div>
    </a>
    <?php endforeach; ?>

    <style>
        .card a:last-of-type { border-bottom: none; }
        .card a:hover > div:last-child > span:first-child { opacity: .85; }
    </style>
</div>
<?php endif; ?>
