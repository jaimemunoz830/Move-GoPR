<!-- =========================================== 
    Aquí ha trabajado:
        Christian J. Lespier
=========================================== -->

<!-- =========================================== 
    Aqui se hace la conexion a la base de datos, 
    se usa de referencia para el resto de páginas
    que necesiten acceder a la base de datos
=========================================== -->
<?php
$host = 'localhost';
$dbname = 'move-gopr';
$username = 'root'; 
$password = ''; 
try {
    $pdo = new PDO("mysql:host=$host;port=3307;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
