<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Необходима авторизация']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'Неверный метод']); exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$userId   = (int)$_SESSION['user_id'];
$quantity = max(1, (int)($body['quantity'] ?? 1));
$db       = getDb();

// Поддерживаем два варианта: по product_size_id или по product_id (берём первый доступный размер)
if (!empty($body['product_size_id'])) {
    $productSizeId = (int)$body['product_size_id'];
} elseif (!empty($body['product_id'])) {
    $productId = (int)$body['product_id'];
    $stmt = $db->prepare(
        "SELECT ProductSizeId FROM ProductSizes WHERE ProductId=? AND Quantity>0 ORDER BY SizeId LIMIT 1"
    );
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        echo json_encode(['success'=>false,'error'=>'Нет доступных размеров']); exit;
    }
    $productSizeId = $row['ProductSizeId'];
} else {
    echo json_encode(['success'=>false,'error'=>'Не указан товар']); exit;
}

// Проверяем остаток
$chk = $db->prepare("SELECT Quantity FROM ProductSizes WHERE ProductSizeId=?");
$chk->bind_param('i', $productSizeId);
$chk->execute();
$stock = $chk->get_result()->fetch_assoc();
$chk->close();
if (!$stock || $stock['Quantity'] < 1) {
    echo json_encode(['success'=>false,'error'=>'Товар недоступен']); exit;
}

// Добавляем или увеличиваем количество
$existing = $db->prepare(
    "SELECT CartItemId, Quantity FROM CartItems WHERE UserId=? AND ProductSizeId=?"
);
$existing->bind_param('ii', $userId, $productSizeId);
$existing->execute();
$row = $existing->get_result()->fetch_assoc();
$existing->close();

if ($row) {
    $newQty = $row['Quantity'] + $quantity;
    $upd = $db->prepare("UPDATE CartItems SET Quantity=? WHERE CartItemId=?");
    $upd->bind_param('ii', $newQty, $row['CartItemId']);
    $upd->execute();
    $upd->close();
} else {
    $ins = $db->prepare("INSERT INTO CartItems (UserId,ProductSizeId,Quantity) VALUES (?,?,?)");
    $ins->bind_param('iii', $userId, $productSizeId, $quantity);
    $ins->execute();
    $ins->close();
}

// Возвращаем обновлённое количество позиций
$cnt = $db->prepare("SELECT COALESCE(SUM(Quantity),0) FROM CartItems WHERE UserId=?");
$cnt->bind_param('i', $userId);
$cnt->execute();
$cnt->bind_result($cartCount);
$cnt->fetch();
$cnt->close();

echo json_encode(['success'=>true, 'cart_count'=>(int)$cartCount]);
