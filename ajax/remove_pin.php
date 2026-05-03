<?php
define('MOVE_GO_APP', true);
require '../config.php';
require_once '../db_queries.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

$role = $_SESSION['role'] ?? 'viewer';
if (!in_array($role, ['admin', 'owner'], true)) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$id   = isset($body['id']) ? (int) $body['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

$ok = removePinLocation($pdo, $id);
echo json_encode(['success' => $ok]);
