 <!-- ===========================================
   Aquí ha trabajado: 
      Esteban G. Echevarria
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
$id   = isset($body['id'])  ? (int)   $body['id']  : 0;
$lat  = isset($body['lat']) ? (float) $body['lat'] : null;
$lng  = isset($body['lng']) ? (float) $body['lng'] : null;
$name = isset($body['name']) ? trim($body['name']) : '';

if (!$id || $lat === null || $lng === null) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

//  ===========================================
  // Creación / actualización del pin
  // Guarda las coordenadas y activa el pin con
  // setPinLocation(). Luego recupera el registro
  // actualizado para devolver el nombre final al
  // cliente (útil si el nombre fue modificado).
// ============================================== 
$ok  = setPinLocation($pdo, $id, $lat, $lng, $name);
$loc = getLocationById($pdo, $id);
echo json_encode(['success' => $ok, 'name' => $loc['name'] ?? '']);
