<?php
/**
 * submit_quote.php
 *
 * Endpoint que recibe datos de cotización vía POST desde las páginas de servicios
 * y los guarda en la tabla quote_requests.
 *
 * Llamado por fetch() desde servicios-mejoras y servicios-mudanzas.
 *
 * @author Jaime A. Muñoz Rodriguez
 */
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit();
}

$nombre        = trim($_POST['nombre']        ?? '');
$telefono      = trim($_POST['telefono']      ?? '');
$email         = trim($_POST['email']         ?? '');
$servicio      = trim($_POST['servicio']      ?? '');
$ubicacion     = trim($_POST['ubicacion']     ?? '');
$mensaje       = trim($_POST['mensaje']       ?? '');
$tipo_servicio = trim($_POST['tipo_servicio'] ?? '');

if (!$nombre || !$telefono) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Nombre y teléfono son requeridos']);
    exit();
}

$stmt = $pdo->prepare("
    INSERT INTO quote_requests (tipo_servicio, nombre, telefono, email, servicio, ubicacion, mensaje, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'new')
");
$stmt->execute([$tipo_servicio, $nombre, $telefono, $email, $servicio, $ubicacion, $mensaje]);

echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
