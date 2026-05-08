<!--  =========================================== 
  Aquí ha trabajado:
    Esteban G Echevarria    
==============================================  -->

<?php
//  ===========================================
  // Configuración inicial
// ============================================== 
define('MOVE_GO_APP', true);
require '../config.php';
require_once '../db_queries.php';

//  ===========================================
  // Inicio de sesión y tipo de respuesta
// ============================================== 
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

//  ===========================================
  // Verificación de permisos
// ============================================== 
$role = $_SESSION['role'] ?? 'viewer';
if (!in_array($role, ['admin', 'owner'], true)) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

//  ===========================================
  // Lectura y validación del cuerpo de la petición
// ============================================== 
$body = json_decode(file_get_contents('php://input'), true);
$id   = isset($body['id']) ? (int) $body['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

//  ===========================================
  // Eliminación del pin
  // Llama a removePinLocation() que desactiva el
  // pin y borra las coordenadas (el registro se
  // conserva). Devuelve true/false como resultado.
// ============================================== 
$ok = removePinLocation($pdo, $id);
echo json_encode(['success' => $ok]);
