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

$userId = (int)$_SESSION['user_id'];
$db     = getDb();

// Получаем позиции корзины
$stmt = $db->prepare(
    "SELECT ci.CartItemId, ci.ProductSizeId, ci.Quantity, p.Price
     FROM CartItems ci
     JOIN ProductSizes ps ON ps.ProductSizeId=ci.ProductSizeId
     JOIN Products p      ON p.ProductId=ps.ProductId
     WHERE ci.UserId=?"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($items)) {
    echo json_encode(['success'=>false,'error'=>'Корзина пуста']); exit;
}

$total = array_sum(array_map(fn($i) => $i['Price'] * $i['Quantity'], $items));

$db->begin_transaction();
try {
    // Создаём заказ
    $ord = $db->prepare("INSERT INTO Orders (UserId, StatusId, TotalAmount) VALUES (?,1,?)");
    $ord->bind_param('id', $userId, $total);
    $ord->execute();
    $orderId = $db->insert_id;
    $ord->close();

    // Создаём состав заказа
    $ins = $db->prepare(
        "INSERT INTO OrderItems (OrderId, ProductSizeId, Quantity, Price) VALUES (?,?,?,?)"
    );
    foreach ($items as $item) {
        $ins->bind_param('iiid', $orderId, $item['ProductSizeId'], $item['Quantity'], $item['Price']);
        $ins->execute();
    }
    $ins->close();

    // Очищаем корзину
    $del = $db->prepare("DELETE FROM CartItems WHERE UserId=?");
    $del->bind_param('i', $userId);
    $del->execute();
    $del->close();

    $db->commit();
    echo json_encode(['success'=>true,'order_id'=>$orderId]);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['success'=>false,'error'=>'Ошибка при создании заказа']);
}
