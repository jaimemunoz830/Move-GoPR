<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['delete_id']) && isset($_GET['table'])) {
    $table = $_GET['table'];
    $id = (int)$_GET['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: db_test.php?msg=deleted");
    exit();
}

$tableQuery = $pdo->query("SHOW TABLES");
$tables = $tableQuery->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Move-GoPR | Database Test Panel</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .test-container { padding: 40px; max-width: 1200px; margin: auto; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 15px; border-radius: 10px; text-align: center; border-bottom: 4px solid #2d6cdf; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .stat-card h4 { margin: 0; color: #666; font-size: 12px; text-transform: uppercase; }
        .stat-card p { font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color: #1e3a8a; }
        
        .db-card { background: white; padding: 25px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f4f6f9; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .delete-link { color: #dc2626; text-decoration: none; font-weight: bold; }
        .delete-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="test-container">
   <?php if (isset($pdo)): ?>
        <div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; border: 1px solid #c3e6cb; margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
            <span style="font-size: 24px;">✅</span>
            <div>
                <strong style="display: block; font-size: 18px;">Conexión Exitosa</strong>
                <span style="font-size: 14px;">El sistema está conectado a la base de datos: <code><?php echo htmlspecialchars($dbname); ?></code></span>
            </div>
        </div>
    <?php else: ?>
        <div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px; border: 1px solid #f5c6cb; margin-bottom: 25px;">
            <strong>❌ Error de Conexión:</strong> No se pudo encontrar la base de datos. Verifica tu archivo <code>config.php</code>.
        </div>
    <?php endif; ?>

    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="margin:0;">MoveAnd<span class="green">GoPR</span> <small style="font-size: 15px; color: #666;">DB Demo</small></h1>
        <b>cuidao con el boton de borrar que funciona</b>
        <div style="display: flex; gap: 10px;">
            <a href="index.php" class="btn blue">Volver al Inicio</a>
        </div>
    </header>

    <div class="stats-grid">
        <?php foreach ($tables as $table): 
            $count = $pdo->query("SELECT count(*) FROM `$table`")->fetchColumn();
        ?>
            <div class="stat-card">
                <h4><?php echo $table; ?></h4>
                <p><?php echo $count; ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <?php foreach ($tables as $table): ?>
        <div class="db-card">
            <h3 style="color: #1e3a8a;">Tabla: <?php echo htmlspecialchars($table); ?></h3>
            <?php
            $stmt = $pdo->query("SELECT * FROM `$table` LIMIT 10");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($rows): ?>
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($rows[0]) as $col): ?>
                                <th><?php echo htmlspecialchars($col); ?></th>
                            <?php endforeach; ?>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?php echo htmlspecialchars($cell ?? 'NULL'); ?></td>
                                <?php endforeach; ?>
                                <td>
                                    <a href="?table=<?php echo $table; ?>&delete_id=<?php echo $row['id']; ?>" 
                                       class="delete-link" 
                                       onclick="return confirm('¿Seguro que quieres borrar esto?')">Borrar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #888; font-style: italic;">No hay datos en esta tabla todavía.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>