<?php
/**
 * users_panel.php
 *
 * Panel de administración para gestionar usuarios: aprobar/rechazar registros
 * pendientes, editar y eliminar. Nuevos registros llegan con status='pending'
 * y el admin decide el acceso desde aquí.
 *
 * @author     Jaime A. Muñoz Rodriguez
 * @version    1.0
 */

// ── POST handlers ──────────────────────────────────────────────────────────

$form_error  = '';
$post_values = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['form_action'] ?? '';

    if ($form_action === 'approve') {
        $uid = (int)$_POST['id'];
        $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$uid]);
        header("Location: dashboard.php?section=users&msg=approved");
        exit();
    }

    if ($form_action === 'reject') {
        $uid = (int)$_POST['id'];
        $pdo->prepare("UPDATE users SET status = 'rejected' WHERE id = ?")->execute([$uid]);
        header("Location: dashboard.php?section=users&msg=rejected");
        exit();
    }

    if ($form_action === 'delete') {
        $uid = (int)$_POST['id'];
        if ($uid === (int)$_SESSION['user_id']) {
            header("Location: dashboard.php?section=users&msg=selfdelete");
        } else {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
            header("Location: dashboard.php?section=users&msg=deleted");
        }
        exit();
    }

    if ($form_action === 'update') {
        $uid      = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $role     = $_POST['role'] ?? 'customer';
        $status   = $_POST['status'] ?? 'active';
        $new_pass = $_POST['new_password'] ?? '';

        if (empty($name) || empty($email)) {
            $form_error = "El nombre y el correo son requeridos.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $form_error = "El correo electrónico no es válido.";
        } elseif (!empty($new_pass) && strlen($new_pass) < 8) {
            $form_error = "La contraseña debe tener al menos 8 caracteres.";
        } else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->execute([$email, $uid]);
            if ($check->fetch()) {
                $form_error = "Ese correo ya está en uso por otro usuario.";
            } else {
                if (!empty($new_pass)) {
                    $pdo->prepare("UPDATE users SET name=?, email=?, role=?, status=?, password_hash=? WHERE id=?")
                        ->execute([$name, $email, $role, $status, password_hash($new_pass, PASSWORD_DEFAULT), $uid]);
                } else {
                    $pdo->prepare("UPDATE users SET name=?, email=?, role=?, status=? WHERE id=?")
                        ->execute([$name, $email, $role, $status, $uid]);
                }
                header("Location: dashboard.php?section=users&msg=updated");
                exit();
            }
        }

        $post_values = compact('uid', 'name', 'email', 'role', 'status');
    }
}

// ── View setup ─────────────────────────────────────────────────────────────

$action  = $_GET['action'] ?? 'list';
$msg     = $_GET['msg'] ?? '';
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!empty($form_error)) {
    $action  = 'edit';
    $edit_id = $post_values['uid'] ?? null;
}

$edit_user = null;
if ($action === 'edit' && $edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$edit_user) {
        header("Location: dashboard.php?section=users&msg=notfound");
        exit();
    }
    if (!empty($post_values)) {
        $edit_user['name']   = $post_values['name'];
        $edit_user['email']  = $post_values['email'];
        $edit_user['role']   = $post_values['role'];
        $edit_user['status'] = $post_values['status'];
    }
}

function user_status_label($status) {
    $map = ['active' => ['#16a34a','Activo'], 'pending' => ['#d97706','Pendiente'], 'rejected' => ['#dc2626','Rechazado']];
    [$color, $label] = $map[$status] ?? ['#94a3b8', $status];
    return "<span style='color:{$color}; font-weight:600;'>{$label}</span>";
}

function user_role_label($role) {
    return $role === 'admin' ? 'Administrador' : 'Cliente';
}

function format_date_es_users($date_str) {
    if (empty($date_str)) return '—';
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $d = new DateTime($date_str);
    return $d->format('j') . ' de ' . $meses[(int)$d->format('n') - 1] . ' de ' . $d->format('Y');
}
?>

<style>
    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .btn-primary {
        background: #1e3a8a; color: white; padding: 9px 18px;
        border: none; border-radius: 6px; text-decoration: none;
        font-size: 14px; cursor: pointer; display: inline-block;
    }
    .btn-primary:hover { background: #1e40af; }
    .btn-secondary {
        background: #475569; color: white; padding: 9px 18px;
        border: none; border-radius: 6px; text-decoration: none;
        font-size: 14px; cursor: pointer; display: inline-block;
    }
    .btn-secondary:hover { background: #334155; }
    .btn-danger {
        background: #dc2626; color: white; padding: 6px 12px;
        border: none; border-radius: 5px; font-size: 13px; cursor: pointer;
    }
    .btn-danger:hover { background: #b91c1c; }
    .btn-edit {
        background: #d97706; color: white; padding: 6px 12px;
        border-radius: 5px; text-decoration: none; font-size: 13px; display: inline-block;
    }
    .btn-edit:hover { background: #b45309; }
    .btn-approve {
        background: #16a34a; color: white; padding: 6px 14px;
        border: none; border-radius: 5px; font-size: 13px; cursor: pointer;
    }
    .btn-approve:hover { background: #15803d; }
    .btn-reject-soft {
        background: #64748b; color: white; padding: 6px 14px;
        border: none; border-radius: 5px; font-size: 13px; cursor: pointer;
    }
    .btn-reject-soft:hover { background: #475569; }

    .alert { padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
    .alert-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    .pending-card {
        background: white;
        border: 1px solid #fcd34d;
        border-left: 4px solid #d97706;
        border-radius: 8px;
        padding: 20px 24px;
        margin-bottom: 24px;
    }
    .pending-card h3 { margin: 0 0 16px; color: #92400e; font-size: 15px; }
    .pending-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #fef3c7;
        flex-wrap: wrap;
        gap: 10px;
    }
    .pending-item:last-child { border-bottom: none; padding-bottom: 0; }
    .pending-item-info { flex: 1; min-width: 180px; }
    .pending-item-info strong { display: block; color: #1e293b; font-size: 14px; }
    .pending-item-info small { color: #64748b; font-size: 13px; }
    .pending-item-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    .users-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .users-table th {
        background: #f4f6f9; padding: 11px 14px;
        text-align: left; border-bottom: 2px solid #ddd; white-space: nowrap;
    }
    .users-table td { padding: 11px 14px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .users-table tr:hover td { background: #f9fafb; }

    .form-card {
        background: white; padding: 28px; border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px;
    }
    .form-card h3 {
        margin: 0 0 20px; color: #1e3a8a; font-size: 16px;
        border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;
    }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-group label { font-size: 13px; font-weight: 600; color: #374151; }
    .form-group input, .form-group select {
        padding: 8px 11px; border: 1px solid #d1d5db;
        border-radius: 6px; font-size: 14px; font-family: inherit; transition: border-color 0.2s;
    }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: #1e3a8a; }
    .form-actions { display: flex; gap: 12px; margin-top: 24px; }

    @media (max-width: 640px) {
        .form-grid { grid-template-columns: 1fr; }
        .users-table thead { display: none; }
        .users-table tr { display: block; border-bottom: 2px solid #ddd; padding: 10px 0; }
        .users-table td { display: flex; justify-content: space-between; align-items: center; padding: 6px 14px; border: none; }
        .users-table td[data-label]::before {
            content: attr(data-label);
            font-weight: bold; color: #64748b; font-size: 12px; flex-shrink: 0; margin-right: 10px;
        }
    }
</style>

<?php if ($action === 'list'): ?>

    <div class="panel-header">
        <h1 style="margin:0;">Usuarios</h1>
    </div>


    <?php
    $pending_users = $pdo->query(
        "SELECT id, name, email, created_at FROM users WHERE status = 'pending' ORDER BY created_at ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if (!empty($pending_users)): ?>
    <div class="pending-card">
        <h3><i class="fa-solid fa-user-clock" style="margin-right:8px;"></i>Registros pendientes (<?= count($pending_users) ?>)</h3>
        <?php foreach ($pending_users as $pu): ?>
        <div class="pending-item">
            <div class="pending-item-info">
                <strong><?= htmlspecialchars($pu['name']) ?></strong>
                <small><?= htmlspecialchars($pu['email']) ?></small>
            </div>
            <?php if (!empty($pu['created_at'])): ?>
            <small style="color:#64748b;"><?= format_date_es_users($pu['created_at']) ?></small>
            <?php endif; ?>
            <div class="pending-item-actions">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="form_action" value="approve">
                    <input type="hidden" name="id" value="<?= $pu['id'] ?>">
                    <button type="submit" class="btn-approve">Aprobar</button>
                </form>
                <form method="POST" style="display:inline;"
                      onsubmit="return confirm('¿Rechazar el registro de <?= htmlspecialchars(addslashes($pu['name'])) ?>?');">
                    <input type="hidden" name="form_action" value="reject">
                    <input type="hidden" name="id" value="<?= $pu['id'] ?>">
                    <button type="submit" class="btn-reject-soft">Rechazar</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php
    $all_users = $pdo->query(
        "SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if (empty($all_users)): ?>
        <div class="card" style="cursor:default;">
            <p style="color:#888; text-align:center;">No hay usuarios registrados.</p>
        </div>
    <?php else: ?>
        <div class="user-grid">
            <?php foreach ($all_users as $u): ?>
            <div class="user-card" data-status="<?= $u['status'] ?>">
                <p class="user-card-name">
                    <?= htmlspecialchars($u['name']) ?>
                    <?php if ((int)$u['id'] === (int)$_SESSION['user_id']): ?>
                        <span style="font-size:12px; color:#94a3b8; font-weight:400;"> (tú)</span>
                    <?php endif; ?>
                </p>
                <p class="user-card-row">
                    <i class="fa-solid fa-envelope"></i>
                    <?= htmlspecialchars($u['email']) ?>
                </p>
                <p class="user-card-row">
                    <i class="fa-solid fa-<?= $u['role'] === 'admin' ? 'shield-halved' : 'user' ?>"></i>
                    <?= user_role_label($u['role']) ?>
                    &nbsp;·&nbsp;
                    <?= user_status_label($u['status']) ?>
                </p>
                <p class="user-card-row" style="color:#94a3b8; font-size:13px;">
                    Registrado el <?= format_date_es_users($u['created_at']) ?>
                </p>
                <div class="user-card-footer">
                    <a href="dashboard.php?section=users&action=edit&id=<?= $u['id'] ?>" class="btn-edit-user">
                        <i class="fa-solid fa-pen-to-square"></i> Editar
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php elseif ($action === 'edit' && $edit_user): ?>

    <div class="panel-header">
        <h1 style="margin:0;">Editar Usuario</h1>
        <a href="dashboard.php?section=users" class="btn-secondary">← Volver al listado</a>
    </div>

    <?php if (!empty($form_error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($form_error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="form_action" value="update">
        <input type="hidden" name="id" value="<?= (int)$edit_user['id'] ?>">

        <div class="form-card">
            <h3>Información</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre completo *</label>
                    <input type="text" name="name"
                           value="<?= htmlspecialchars($edit_user['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Correo electrónico *</label>
                    <input type="email" name="email"
                           value="<?= htmlspecialchars($edit_user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Rol</label>
                    <select name="role">
                        <option value="admin"    <?= $edit_user['role'] === 'admin'    ? 'selected' : '' ?>>Admin</option>
                        <option value="customer" <?= $edit_user['role'] === 'customer' ? 'selected' : '' ?>>Cliente</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select name="status">
                        <option value="active"   <?= $edit_user['status'] === 'active'   ? 'selected' : '' ?>>Activo</option>
                        <option value="rejected" <?= $edit_user['status'] === 'rejected' ? 'selected' : '' ?>>Rechazado</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h3>Contraseña</h3>
            <div class="form-group" style="max-width: 400px;">
                <label>Nueva contraseña <small style="color:#888; font-weight:normal;">(dejar en blanco para no cambiar)</small></label>
                <input type="password" name="new_password" minlength="8" placeholder="Mínimo 8 caracteres">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Guardar cambios</button>
            <a href="dashboard.php?section=users" class="btn-secondary">Cancelar</a>
        </div>
    </form>

    <?php if ((int)$edit_user['id'] !== (int)$_SESSION['user_id']): ?>
    <div class="form-card" style="border:1px solid #fee2e2; margin-top:8px;">
        <h3 style="color:#dc2626;">Zona de peligro</h3>
        <p style="font-size:13px; color:#64748b; margin:0 0 16px;">Eliminar este usuario es permanente y no se puede deshacer.</p>
        <form method="POST"
              onsubmit="return confirm('¿Eliminar a <?= htmlspecialchars(addslashes($edit_user['name'])) ?>? Esta acción no se puede deshacer.');">
            <input type="hidden" name="form_action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$edit_user['id'] ?>">
            <button type="submit" class="btn-danger">Eliminar usuario</button>
        </form>
    </div>
    <?php endif; ?>

<?php endif; ?>
