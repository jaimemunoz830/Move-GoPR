<?php
/**
 * quotes_panel.php
 *
 * Panel de administración para gestionar solicitudes de cotización.
 * Muestra todos los tickets recibidos desde servicios-mejoras y servicios-mudanzas.
 * Permite ver en detalle cada solicitud y cambiar su estado.
 *
 * @author Esteban G. Echevarria Perez
 * @author Jaime A. Muñoz Rodriguez
 * @version 1.1
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quote_action'])) {
    $quote_id   = (int)($_POST['quote_id'] ?? 0);
    $back_filter = $_POST['back_filter'] ?? 'all';

    if ($_POST['quote_action'] === 'update_status') {
        $new_status = $_POST['new_status'] ?? '';
        $allowed_statuses = ['new', 'contacted', 'closed'];
        if ($quote_id && in_array($new_status, $allowed_statuses)) {
            $pdo->prepare("UPDATE quote_requests SET status = ? WHERE id = ?")->execute([$new_status, $quote_id]);
        }
        header("Location: dashboard.php?section=quotes&action=view&id={$quote_id}&filter=" . urlencode($back_filter) . "&msg=status_updated");
        exit();
    }

    if ($_POST['quote_action'] === 'save_notes') {
        $notes = trim($_POST['notes'] ?? '');
        $pdo->prepare("UPDATE quote_requests SET notes = ? WHERE id = ?")->execute([$notes, $quote_id]);
        header("Location: dashboard.php?section=quotes&action=view&id={$quote_id}&filter=" . urlencode($back_filter) . "&msg=notes_saved");
        exit();
    }

    if ($_POST['quote_action'] === 'delete') {
        $pdo->prepare("DELETE FROM quote_requests WHERE id = ?")->execute([$quote_id]);
        header("Location: dashboard.php?section=quotes&filter=" . urlencode($back_filter) . "&msg=quote_deleted");
        exit();
    }
}

$action   = $_GET['action'] ?? 'list';
$quote_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$filter   = $_GET['filter'] ?? 'all';
$allowed_filters = ['all', 'new', 'contacted', 'closed'];
if (!in_array($filter, $allowed_filters)) $filter = 'all';

function quote_status_badge($status) {
    $map = [
        'new'       => ['color' => '#d97706', 'label' => 'Nueva'],
        'contacted' => ['color' => '#1e3a8a', 'label' => 'Contactado'],
        'closed'    => ['color' => '#16a34a', 'label' => 'Cerrado'],
    ];
    $s = $map[$status] ?? ['color' => '#94a3b8', 'label' => $status];
    return "<span style='background:{$s['color']}; color:white; padding:4px 12px; border-radius:12px; font-size:13px; white-space:nowrap;'>{$s['label']}</span>";
}

function tipo_badge($tipo) {
    $color = $tipo === 'mudanza' ? '#0891b2' : '#7c3aed';
    $label = $tipo === 'mudanza' ? 'Mudanza' : 'Mejoras';
    return "<span style='background:{$color}; color:white; padding:2px 8px; border-radius:10px; font-size:11px;'>{$label}</span>";
}
?>

<style>
    .quotes-filters { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
    .filter-btn {
        padding: 7px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        background: white;
        cursor: pointer;
        font-size: 13px;
        text-decoration: none;
        color: #374151;
        transition: all 0.15s;
    }
    .filter-btn:hover  { border-color: #1e3a8a; color: #1e3a8a; }
    .filter-btn.active { background: #1e3a8a; color: white; border-color: #1e3a8a; }

    .quote-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .quote-table th {
        background: #f4f6f9;
        padding: 11px 14px;
        text-align: left;
        border-bottom: 2px solid #ddd;
        white-space: nowrap;
    }
    .quote-table td { padding: 11px 14px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .quote-table tbody tr { cursor: pointer; }
    .quote-table tbody tr:hover td { background: #f0f4ff; }

    .mensaje-cell {
        max-width: 200px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        color: #555;
        font-size: 13px;
    }

    /* Detail view */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }
    .detail-field { display: flex; flex-direction: column; gap: 4px; }
    .detail-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; }
    .detail-value { font-size: 15px; color: #1e293b; }
    .detail-mensaje {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        font-size: 14px;
        color: #374151;
        line-height: 1.6;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .status-select {
        padding: 8px 12px;
        border: 2px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        background: white;
        margin-top: 8px;
    }
    .status-select[data-status="new"]       { border-color: #d97706; color: #d97706; }
    .status-select[data-status="contacted"] { border-color: #1e3a8a; color: #1e3a8a; }
    .status-select[data-status="closed"]    { border-color: #16a34a; color: #16a34a; }

    .btn-back {
        background: #475569;
        color: white;
        padding: 9px 18px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        display: inline-block;
        margin-bottom: 20px;
    }
    .btn-back:hover { background: #334155; color: white; }
</style>

<?php if ($action === 'view' && $quote_id): ?>

    <?php
    $stmt = $pdo->prepare("SELECT * FROM quote_requests WHERE id = ?");
    $stmt->execute([$quote_id]);
    $q = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$q) {
        echo '<p style="color:#dc2626;">Solicitud no encontrada.</p>';
        echo '<a href="dashboard.php?section=quotes" class="btn-back">← Volver</a>';
        return;
    }
    ?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <a href="dashboard.php?section=quotes&filter=<?= urlencode($filter) ?>" class="btn-back" style="margin-bottom:0;">← Volver a Solicitudes</a>
        <form method="POST" onsubmit="return confirm('¿Eliminar esta solicitud? Esta acción no se puede deshacer.');">
            <input type="hidden" name="quote_action" value="delete">
            <input type="hidden" name="quote_id" value="<?= $q['id'] ?>">
            <input type="hidden" name="back_filter" value="<?= htmlspecialchars($filter) ?>">
            <button type="submit" style="background:#dc2626; color:white; border:none; padding:9px 18px; border-radius:6px; font-size:13px; cursor:pointer;">
                Eliminar solicitud
            </button>
        </form>
    </div>

    <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
        <h1 style="margin:0;">Solicitud #<?= $q['id'] ?></h1>
        <?= tipo_badge($q['tipo_servicio']) ?>
        <?= quote_status_badge($q['status']) ?>
    </div>

    <div class="card" style="cursor:default; margin-bottom:20px;">
        <h3 style="margin:0 0 20px; color:#1e3a8a; font-size:15px; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">
            Información del Cliente
        </h3>
        <div class="detail-grid">
            <div class="detail-field">
                <span class="detail-label">Nombre</span>
                <span class="detail-value"><?= htmlspecialchars($q['nombre']) ?></span>
            </div>
            <div class="detail-field">
                <span class="detail-label">Teléfono</span>
                <span class="detail-value">
                    <a href="tel:<?= htmlspecialchars($q['telefono']) ?>" style="color:#1e3a8a; text-decoration:none;">
                        📞 <?= htmlspecialchars($q['telefono']) ?>
                    </a>
                </span>
            </div>
            <div class="detail-field">
                <span class="detail-label">Email</span>
                <span class="detail-value">
                    <?php if ($q['email']): ?>
                        <a href="mailto:<?= htmlspecialchars($q['email']) ?>" style="color:#1e3a8a; text-decoration:none;">
                            ✉ <?= htmlspecialchars($q['email']) ?>
                        </a>
                    <?php else: ?>
                        <span style="color:#94a3b8;">—</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-field">
                <span class="detail-label">Fecha de solicitud</span>
                <span class="detail-value"><?= date('d/m/Y g:ia', strtotime($q['created_at'])) ?></span>
            </div>
        </div>
    </div>

    <div class="card" style="cursor:default; margin-bottom:20px;">
        <h3 style="margin:0 0 20px; color:#1e3a8a; font-size:15px; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">
            Detalles del Servicio
        </h3>
        <div class="detail-grid" style="margin-bottom:20px;">
            <div class="detail-field">
                <span class="detail-label">Servicio</span>
                <span class="detail-value"><?= htmlspecialchars($q['servicio'] ?: '—') ?></span>
            </div>
            <div class="detail-field">
                <span class="detail-label">Ubicación</span>
                <span class="detail-value"><?= htmlspecialchars($q['ubicacion'] ?: '—') ?></span>
            </div>
        </div>
        <div class="detail-field">
            <span class="detail-label">Mensaje</span>
            <div class="detail-mensaje" style="margin-top:6px;">
                <?= htmlspecialchars($q['mensaje'] ?: '—') ?>
            </div>
        </div>
    </div>

    <div class="card" style="cursor:default; margin-bottom:20px;">
        <h3 style="margin:0 0 12px; color:#1e3a8a; font-size:15px; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">
            Estado del Ticket
        </h3>
        <p style="color:#555; font-size:14px; margin:0 0 4px;">Estado actual: <?= quote_status_badge($q['status']) ?></p>
        <form method="POST">
            <input type="hidden" name="quote_action" value="update_status">
            <input type="hidden" name="quote_id" value="<?= $q['id'] ?>">
            <input type="hidden" name="back_filter" value="<?= htmlspecialchars($filter) ?>">
            <select name="new_status" class="status-select"
                    data-status="<?= $q['status'] ?>"
                    onchange="this.form.submit()">
                <option value="new"       <?= $q['status'] === 'new'       ? 'selected' : '' ?>>Nueva</option>
                <option value="contacted" <?= $q['status'] === 'contacted' ? 'selected' : '' ?>>Contactado</option>
                <option value="closed"    <?= $q['status'] === 'closed'    ? 'selected' : '' ?>>Cerrado</option>
            </select>
        </form>
    </div>

    <div class="card" style="cursor:default;">
        <h3 style="margin:0 0 8px; color:#1e3a8a; font-size:15px; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">
            Notas internas
        </h3>
        <p style="color:#94a3b8; font-size:13px; margin:0 0 12px;">Solo visible para administradores.</p>
        <form method="POST">
            <input type="hidden" name="quote_action" value="save_notes">
            <input type="hidden" name="quote_id" value="<?= $q['id'] ?>">
            <input type="hidden" name="back_filter" value="<?= htmlspecialchars($filter) ?>">
            <textarea name="notes" rows="4"
                      placeholder="Agrega notas sobre este cliente o el seguimiento..."
                      style="width:100%; padding:10px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:14px; font-family:inherit; resize:vertical; outline:none; box-sizing:border-box; transition:border-color 0.2s;"
                      onfocus="this.style.borderColor='#1e3a8a'" onblur="this.style.borderColor='#d1d5db'"><?= htmlspecialchars($q['notes'] ?? '') ?></textarea>
            <button type="submit"
                    style="margin-top:10px; background:#1e3a8a; color:white; border:none; padding:9px 20px; border-radius:6px; font-size:14px; cursor:pointer;">
                Guardar notas
            </button>
        </form>
    </div>

<?php else: ?>

    <?php
    $where = $filter !== 'all' ? "WHERE status = " . $pdo->quote($filter) : "";
    $quotes = $pdo->query("SELECT * FROM quote_requests $where ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

    $counts    = $pdo->query("SELECT status, COUNT(*) as total FROM quote_requests GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
    $total_all = array_sum($counts);
    ?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="margin:0;">Solicitudes</h1>
        <span style="color:#888; font-size:14px;"><?= $total_all ?> solicitudes en total</span>
    </div>

    <div class="quotes-filters">
        <a href="dashboard.php?section=quotes&filter=all"
           class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">
            Todas (<?= $total_all ?>)
        </a>
        <a href="dashboard.php?section=quotes&filter=new"
           class="filter-btn <?= $filter === 'new' ? 'active' : '' ?>">
            Nuevas (<?= $counts['new'] ?? 0 ?>)
        </a>
        <a href="dashboard.php?section=quotes&filter=contacted"
           class="filter-btn <?= $filter === 'contacted' ? 'active' : '' ?>">
            Contactados (<?= $counts['contacted'] ?? 0 ?>)
        </a>
        <a href="dashboard.php?section=quotes&filter=closed"
           class="filter-btn <?= $filter === 'closed' ? 'active' : '' ?>">
            Cerrados (<?= $counts['closed'] ?? 0 ?>)
        </a>
    </div>

    <?php if (empty($quotes)): ?>
        <div class="card" style="cursor:default;">
            <p style="color:#888; text-align:center;">
                No hay solicitudes <?= $filter !== 'all' ? "con estado «{$filter}»" : '' ?>.
            </p>
        </div>
    <?php else: ?>
        <div class="quote-grid">
            <?php foreach ($quotes as $q): ?>
            <div class="quote-card"
                 data-status="<?= $q['status'] ?>"
                 onclick="window.location='dashboard.php?section=quotes&action=view&id=<?= $q['id'] ?>&filter=<?= urlencode($filter) ?>'">
                <div class="qc-header">
                    <div class="qc-badges">
                        <?= tipo_badge($q['tipo_servicio']) ?>
                        <?= quote_status_badge($q['status']) ?>
                    </div>
                    <span class="qc-date"><?= date('d/m/Y', strtotime($q['created_at'])) ?></span>
                </div>
                <p class="qc-name"><?= htmlspecialchars($q['nombre']) ?></p>
                <div class="qc-contact">
                    <span><i class="fa-solid fa-phone" style="width:14px; color:#94a3b8;"></i> <?= htmlspecialchars($q['telefono']) ?></span>
                    <?php if ($q['email']): ?>
                        <span><i class="fa-solid fa-envelope" style="width:14px; color:#94a3b8;"></i> <?= htmlspecialchars($q['email']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($q['servicio']): ?>
                    <p class="qc-service"><i class="fa-solid fa-wrench" style="width:14px; color:#94a3b8;"></i> <?= htmlspecialchars($q['servicio']) ?></p>
                <?php endif; ?>
                <?php if ($q['ubicacion']): ?>
                    <p class="qc-location"><i class="fa-solid fa-location-dot" style="width:14px; color:#94a3b8;"></i> <?= htmlspecialchars($q['ubicacion']) ?></p>
                <?php endif; ?>
                <?php if ($q['mensaje']): ?>
                    <p class="qc-message">"<?= htmlspecialchars(mb_strimwidth($q['mensaje'], 0, 80, '…')) ?>"</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>
