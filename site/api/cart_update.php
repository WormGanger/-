<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Необходима авторизация']); exit;
}

$body       = json_decode(file_get_contents('php://input'), true);
$cartItemId = (int)($body['cart_item_id'] ?? 0);
$quantity   = max(1, (int)($body['quantity'] ?? 1));
$userId     = (int)$_SESSION['user_id'];
$db         = getDb();

$stmt = $db->prepare("UPDATE CartItems SET Quantity=? WHERE CartItemId=? AND UserId=?");
$stmt->bind_param('iii', $quantity, $cartItemId, $userId);
$stmt->execute();
$stmt->close();

$cnt = $db->prepare("SELECT COALESCE(SUM(Quantity),0) FROM CartItems WHERE UserId=?");
$cnt->bind_param('i', $userId);
$cnt->execute();
$cnt->bind_result($cartCount);
$cnt->fetch();
$cnt->close();

echo json_encode(['success'=>true,'cart_count'=>(int)$cartCount]);
