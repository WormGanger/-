<?php
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /login.php?redirect=cart.php'); exit;
}

$db     = getDb();
$userId = (int)$_SESSION['user_id'];

// Позиции корзины с полной информацией о товаре
$stmt = $db->prepare(
    "SELECT ci.CartItemId, ci.Quantity, ci.ProductSizeId,
            p.ProductId, p.ProductName, p.Price,
            s.SizeName, pi.ImagePath, c.CategoryName
     FROM CartItems ci
     JOIN ProductSizes ps ON ps.ProductSizeId=ci.ProductSizeId
     JOIN Products p      ON p.ProductId=ps.ProductId
     JOIN Sizes s         ON s.SizeId=ps.SizeId
     LEFT JOIN ProductImages pi ON pi.ProductId=p.ProductId AND pi.IsMain=1
     JOIN Categories c    ON c.CategoryId=p.CategoryId
     WHERE ci.UserId=?
     ORDER BY ci.AddedAt DESC"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total    = array_sum(array_map(fn($i) => $i['Price'] * $i['Quantity'], $items));
$delivery = ($total > 0 && $total < 5000) ? 500 : 0;

include __DIR__ . '/includes/header.php';
?>

<main class="cart-main">
  <div class="container">
    <h1 class="page-title">КОРЗИНА</h1>

    <?php if (empty($items)): ?>
      <div class="empty-state">
        <i class="bx bx-cart"></i>
        <h3>Корзина пуста</h3>
        <p>Добавьте товары из каталога</p>
        <a href="/catalog.php" class="btn-primary">Перейти в каталог</a>
      </div>
    <?php else: ?>

    <div class="cart-layout">
      <!-- СПИСОК ПОЗИЦИЙ -->
      <div class="cart-items" id="cartItemsList">
        <div class="cart-header d-flex">
          <span style="flex:2">Товар</span>
          <span>Цена</span>
          <span>Количество</span>
          <span>Итого</span>
          <span></span>
        </div>

        <?php foreach ($items as $i => $item): ?>
        <div class="cart-row d-flex" id="row-<?= $item['CartItemId'] ?>">
          <div class="cart-product" style="flex:2">
            <img src="<?= placeholderSvg($item['ProductName'], $i) ?>"
                 alt="<?= htmlspecialchars($item['ProductName']) ?>"/>
            <div class="cart-product-info">
              <a href="/product.php?id=<?= $item['ProductId'] ?>">
                <h4><?= htmlspecialchars($item['ProductName']) ?></h4>
              </a>
              <p class="cart-size">Размер: <?= htmlspecialchars($item['SizeName']) ?></p>
              <p class="cart-cat"><?= htmlspecialchars($item['CategoryName']) ?></p>
            </div>
          </div>
          <div class="cart-price">
            <?= number_format($item['Price'], 0, '.', ' ') ?> ₽
          </div>
          <div class="cart-qty">
            <div class="qty-control">
              <button class="qty-btn cart-qty-btn"
                      data-id="<?= $item['CartItemId'] ?>" data-action="minus">−</button>
              <span class="qty-val" id="qty-<?= $item['CartItemId'] ?>"><?= $item['Quantity'] ?></span>
              <button class="qty-btn cart-qty-btn"
                      data-id="<?= $item['CartItemId'] ?>" data-action="plus">+</button>
            </div>
          </div>
          <div class="cart-subtotal" id="sub-<?= $item['CartItemId'] ?>">
            <?= number_format($item['Price'] * $item['Quantity'], 0, '.', ' ') ?> ₽
          </div>
          <div class="cart-remove">
            <button class="btn-remove" data-id="<?= $item['CartItemId'] ?>">
              <i class="bx bx-trash"></i>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- ИТОГ -->
      <div class="cart-summary">
        <h3>Итого</h3>
        <div class="summary-line">
          <span>Товары</span>
          <span id="summarySubtotal"><?= number_format($total, 0, '.', ' ') ?> ₽</span>
        </div>
        <div class="summary-line">
          <span>Доставка</span>
          <span id="summaryDelivery">
            <?= $delivery > 0 ? number_format($delivery, 0, '.', ' ').' ₽' : 'Бесплатно' ?>
          </span>
        </div>
        <hr/>
        <div class="summary-total">
          <span>К оплате</span>
          <span id="summaryTotal"><?= number_format($total + $delivery, 0, '.', ' ') ?> ₽</span>
        </div>
        <button class="btn-primary btn-checkout" id="checkoutBtn">
          Оформить заказ
        </button>
        <a href="/catalog.php" class="btn-link">← Продолжить покупки</a>

        <div id="orderSuccess" class="order-success" style="display:none">
          <i class="bx bx-check-circle"></i>
          <h4>Заказ оформлен!</h4>
          <p>Ожидайте подтверждения на email</p>
        </div>
      </div>
    </div>

    <?php endif; ?>
  </div>
</main>

<script>
const prices = {
  <?php foreach($items as $item): ?>
  '<?= $item['CartItemId'] ?>': <?= $item['Price'] ?>,
  <?php endforeach; ?>
};

function recalcTotal() {
  let subtotal = 0;
  document.querySelectorAll('.qty-val').forEach(el => {
    const id  = el.id.replace('qty-', '');
    const qty = +el.textContent;
    const sub = prices[id] * qty;
    const subEl = document.getElementById('sub-' + id);
    if (subEl) subEl.textContent = sub.toLocaleString('ru-RU') + ' ₽';
    subtotal += sub;
  });
  document.getElementById('summarySubtotal').textContent = subtotal.toLocaleString('ru-RU') + ' ₽';
  const delivery = subtotal > 0 && subtotal < 5000 ? 500 : 0;
  document.getElementById('summaryDelivery').textContent = delivery > 0 ? delivery.toLocaleString('ru-RU') + ' ₽' : 'Бесплатно';
  document.getElementById('summaryTotal').textContent = (subtotal + delivery).toLocaleString('ru-RU') + ' ₽';
}

// Кнопки +/−
document.querySelectorAll('.cart-qty-btn').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id     = btn.dataset.id;
    const action = btn.dataset.action;
    const qtyEl  = document.getElementById('qty-' + id);
    let qty = +qtyEl.textContent;
    if (action === 'minus' && qty <= 1) return;
    qty = action === 'plus' ? qty + 1 : qty - 1;
    const res = await fetch('/api/cart_update.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({cart_item_id: id, quantity: qty})
    });
    const data = await res.json();
    if (data.success) {
      qtyEl.textContent = qty;
      document.querySelectorAll('.cart-count').forEach(el => el.textContent = data.cart_count);
      recalcTotal();
    }
  });
});

// Удалить позицию
document.querySelectorAll('.btn-remove').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm('Удалить товар из корзины?')) return;
    const id = btn.dataset.id;
    const res = await fetch('/api/cart_remove.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({cart_item_id: id})
    });
    const data = await res.json();
    if (data.success) {
      const row = document.getElementById('row-' + id);
      row.style.opacity = '0';
      row.style.transform = 'translateX(-20px)';
      setTimeout(() => row.remove(), 300);
      delete prices[id];
      document.querySelectorAll('.cart-count').forEach(el => el.textContent = data.cart_count);
      recalcTotal();
      if (document.querySelectorAll('.cart-row').length === 0) location.reload();
    }
  });
});

// Оформить заказ
document.getElementById('checkoutBtn')?.addEventListener('click', async () => {
  const btn = document.getElementById('checkoutBtn');
  btn.disabled = true;
  btn.textContent = 'Оформляем...';
  const res = await fetch('/api/order_place.php', {method:'POST'});
  const data = await res.json();
  if (data.success) {
    document.getElementById('orderSuccess').style.display = 'flex';
    document.getElementById('checkoutBtn').style.display = 'none';
    document.querySelectorAll('.cart-count').forEach(el => el.textContent = '0');
    setTimeout(() => location.reload(), 3000);
  } else {
    btn.disabled = false;
    btn.textContent = 'Оформить заказ';
    alert(data.error || 'Ошибка при оформлении заказа');
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
