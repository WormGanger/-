<?php
require_once __DIR__ . '/config/db.php';
$db = getDb();

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$productId) { header('Location: /catalog.php'); exit; }

// Товар
$stmt = $db->prepare(
    "SELECT p.*, c.CategoryName FROM Products p
     JOIN Categories c ON c.CategoryId=p.CategoryId
     WHERE p.ProductId=? AND p.IsActive=1"
);
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) { header('Location: /catalog.php'); exit; }

// Изображения товара
$imgStmt = $db->prepare("SELECT * FROM ProductImages WHERE ProductId=? ORDER BY IsMain DESC");
$imgStmt->bind_param('i', $productId);
$imgStmt->execute();
$images = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$imgStmt->close();

// Размеры с остатком
$sizeStmt = $db->prepare(
    "SELECT ps.ProductSizeId, s.SizeId, s.SizeName, ps.Quantity
     FROM ProductSizes ps
     JOIN Sizes s ON s.SizeId=ps.SizeId
     WHERE ps.ProductId=?
     ORDER BY s.SizeId"
);
$sizeStmt->bind_param('i', $productId);
$sizeStmt->execute();
$sizes = $sizeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$sizeStmt->close();

// Похожие товары (та же категория, без текущего)
$relStmt = $db->prepare(
    "SELECT p.ProductId, p.ProductName, p.Price, pi.ImagePath
     FROM Products p
     LEFT JOIN ProductImages pi ON pi.ProductId=p.ProductId AND pi.IsMain=1
     WHERE p.CategoryId=? AND p.ProductId!=? AND p.IsActive=1
     LIMIT 4"
);
$relStmt->bind_param('ii', $product['CategoryId'], $productId);
$relStmt->execute();
$related = $relStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$relStmt->close();

include __DIR__ . '/includes/header.php';
?>

<main class="product-main">
  <div class="container">
    <!-- Хлебные крошки -->
    <nav class="breadcrumb">
      <a href="/index.php">Главная</a> /
      <a href="/catalog.php">Каталог</a> /
      <a href="/catalog.php?category=<?= $product['CategoryId'] ?>"><?= htmlspecialchars($product['CategoryName']) ?></a> /
      <span><?= htmlspecialchars($product['ProductName']) ?></span>
    </nav>

    <div class="product-detail">
      <!-- ГАЛЕРЕЯ -->
      <div class="product-gallery">
        <div class="gallery-main">
          <img id="mainImg"
               src="<?= placeholderSvg($product['ProductName'], $productId % 8) ?>"
               alt="<?= htmlspecialchars($product['ProductName']) ?>"/>
        </div>
        <?php if (count($images) > 1): ?>
        <div class="gallery-thumbs">
          <?php foreach ($images as $j => $img): ?>
          <img src="<?= placeholderSvg($product['ProductName'], ($productId + $j) % 8) ?>"
               alt="Фото <?= $j+1 ?>"
               class="thumb <?= $j==0?'active':'' ?>"
               onclick="document.getElementById('mainImg').src=this.src; document.querySelectorAll('.thumb').forEach(t=>t.classList.remove('active')); this.classList.add('active');"/>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- ИНФОРМАЦИЯ -->
      <div class="product-details">
        <span class="product-cat-label"><?= htmlspecialchars($product['CategoryName']) ?></span>
        <h1 class="product-title"><?= htmlspecialchars($product['ProductName']) ?></h1>
        <p class="product-price"><?= number_format($product['Price'], 0, '.', ' ') ?> ₽</p>

        <div class="product-desc">
          <p><?= nl2br(htmlspecialchars($product['Description'] ?? '')) ?></p>
        </div>

        <!-- Выбор размера -->
        <div class="size-selector">
          <h3>Размер:</h3>
          <div class="size-buttons">
            <?php foreach ($sizes as $size): ?>
            <button class="size-btn <?= $size['Quantity']==0?'disabled':'' ?>"
                    data-size-id="<?= $size['ProductSizeId'] ?>"
                    <?= $size['Quantity']==0?'disabled':'' ?>>
              <?= htmlspecialchars($size['SizeName']) ?>
              <?php if ($size['Quantity']==0): ?>
                <span class="out-of-stock">×</span>
              <?php elseif ($size['Quantity'] < 4): ?>
                <span class="low-stock">!</span>
              <?php endif; ?>
            </button>
            <?php endforeach; ?>
          </div>
          <p class="size-note" id="sizeNote"></p>
        </div>

        <!-- Количество -->
        <div class="qty-selector">
          <h3>Количество:</h3>
          <div class="qty-control">
            <button class="qty-btn" id="qtyMinus">−</button>
            <input type="number" id="qtyInput" value="1" min="1" max="99" readonly/>
            <button class="qty-btn" id="qtyPlus">+</button>
          </div>
        </div>

        <!-- Кнопки -->
        <div class="product-actions">
          <button class="btn-primary btn-add-cart" id="addToCartBtn" data-product="<?= $productId ?>">
            <i class="bx bx-cart-add"></i> Добавить в корзину
          </button>
          <a href="/cart.php" class="btn-outline">Перейти в корзину</a>
        </div>

        <div class="product-meta">
          <p><i class="bx bx-package"></i> Бесплатная доставка от 5000 ₽</p>
          <p><i class="bx bx-refresh"></i> Возврат в течение 30 дней</p>
        </div>
      </div>
    </div>

    <!-- ПОХОЖИЕ ТОВАРЫ -->
    <?php if (!empty($related)): ?>
    <section class="related-products">
      <h2 class="section-title">ПОХОЖИЕ ТОВАРЫ</h2>
      <div class="products-grid">
        <?php foreach ($related as $i => $rp): ?>
        <div class="product-card">
          <a href="/product.php?id=<?= $rp['ProductId'] ?>">
            <div class="product-img">
              <img src="<?= placeholderSvg($rp['ProductName'], $i+4) ?>"
                   alt="<?= htmlspecialchars($rp['ProductName']) ?>"/>
              <div class="product-overlay"><span>Подробнее</span></div>
            </div>
            <div class="product-info">
              <h4><?= htmlspecialchars($rp['ProductName']) ?></h4>
              <p class="price"><?= number_format($rp['Price'], 0, '.', ' ') ?> ₽</p>
            </div>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </div>
</main>

<script>
let selectedSizeId = null;

// Выбор размера
document.querySelectorAll('.size-btn:not([disabled])').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    selectedSizeId = btn.dataset.sizeId;
    document.getElementById('sizeNote').textContent = '';
  });
});

// Количество
const qtyInput = document.getElementById('qtyInput');
document.getElementById('qtyMinus').addEventListener('click', () => {
  if (qtyInput.value > 1) qtyInput.value--;
});
document.getElementById('qtyPlus').addEventListener('click', () => {
  qtyInput.value = +qtyInput.value + 1;
});

// Добавить в корзину
document.getElementById('addToCartBtn').addEventListener('click', async () => {
  <?php if (!$isLoggedIn): ?>
    window.location.href = '/login.php?redirect=product.php?id=<?= $productId ?>';
    return;
  <?php endif; ?>
  if (!selectedSizeId) {
    document.getElementById('sizeNote').textContent = '← Выберите размер';
    document.getElementById('sizeNote').style.color = '#e74c3c';
    return;
  }
  const btn = document.getElementById('addToCartBtn');
  btn.disabled = true;
  const res = await fetch('/api/cart_add.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({product_size_id: selectedSizeId, quantity: +qtyInput.value})
  });
  const data = await res.json();
  btn.disabled = false;
  if (data.success) {
    document.querySelectorAll('.cart-count').forEach(el => el.textContent = data.cart_count);
    btn.innerHTML = '<i class="bx bx-check"></i> Добавлено!';
    btn.classList.add('added');
    setTimeout(()=>{
      btn.innerHTML='<i class="bx bx-cart-add"></i> Добавить в корзину';
      btn.classList.remove('added');
    }, 2500);
  } else {
    alert(data.error || 'Ошибка добавления');
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
