<?php
require_once __DIR__ . '/config/db.php';
$db = getDb();

// Категории для блока категорий
$categories = $db->query("SELECT * FROM Categories ORDER BY CategoryId")->fetch_all(MYSQLI_ASSOC);

// Новые поступления (последние 4 активных товара)
$newProducts = $db->query(
    "SELECT p.*, pi.ImagePath
     FROM Products p
     LEFT JOIN ProductImages pi ON pi.ProductId=p.ProductId AND pi.IsMain=1
     WHERE p.IsActive=1
     ORDER BY p.CreatedAt DESC
     LIMIT 4"
)->fetch_all(MYSQLI_ASSOC);

// Все товары каталога (8 штук для секции «Коллекция»)
$featuredProducts = $db->query(
    "SELECT p.*, pi.ImagePath
     FROM Products p
     LEFT JOIN ProductImages pi ON pi.ProductId=p.ProductId AND pi.IsMain=1
     WHERE p.IsActive=1
     LIMIT 8"
)->fetch_all(MYSQLI_ASSOC);

// Слайды: берём первые 3 категории как слайды
$slides = [
    ['title'=>'НОВАЯ КОЛЛЕКЦИЯ 2026', 'subtitle'=>'ВЕСНА / ЛЕТО', 'btn'=>'Смотреть коллекцию'],
    ['title'=>'ВЕРХНЯЯ ОДЕЖДА',       'subtitle'=>'АВТОРСКИЙ СТИЛЬ БРЕНДА', 'btn'=>'В каталог'],
    ['title'=>'АКСЕССУАРЫ',           'subtitle'=>'ДЕТАЛИ ДЕЛАЮТ ОБРАЗ',    'btn'=>'Выбрать'],
];

include __DIR__ . '/includes/header.php';
?>

<!-- HERO SLIDER -->
<header class="header" id="header">
  <div class="hero">
    <div class="glide" id="glide_1">
      <div class="glide__track" data-glide-el="track">
        <ul class="glide__slides">
          <?php foreach ($slides as $i => $slide): ?>
          <li class="glide__slide">
            <div class="center">
              <div class="left">
                <span><?= htmlspecialchars($slide['subtitle']) ?></span>
                <h1><?= htmlspecialchars($slide['title']) ?></h1>
                <a href="/catalog.php" class="hero-btn"><?= htmlspecialchars($slide['btn']) ?></a>
              </div>
              <div class="right">
                <img src="<?= placeholderSliderSvg($slide['title'], $i) ?>"
                     alt="<?= htmlspecialchars($slide['title']) ?>" class="hero-placeholder"/>
              </div>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="glide__bullets" data-glide-el="controls[nav]">
        <?php for ($i=0; $i<count($slides); $i++): ?>
          <button class="glide__bullet" data-glide-dir="=<?= $i ?>"></button>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</header>

<!-- CATEGORIES -->
<section class="section category">
  <div class="cat-center">
    <?php foreach ($categories as $i => $cat): ?>
    <div class="cat">
      <a href="/catalog.php?category=<?= $cat['CategoryId'] ?>">
        <div class="cat-img">
          <img src="<?= placeholderSvg($cat['CategoryName'], $i) ?>"
               alt="<?= htmlspecialchars($cat['CategoryName']) ?>"/>
        </div>
        <h3><?= htmlspecialchars($cat['CategoryName']) ?></h3>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- NEW ARRIVALS -->
<section class="section new-arrivals">
  <div class="container">
    <h2 class="section-title">НОВЫЕ ПОСТУПЛЕНИЯ</h2>
    <div class="products-grid">
      <?php if (empty($newProducts)): ?>
        <p class="empty-msg">Товары не найдены.</p>
      <?php else: ?>
        <?php foreach ($newProducts as $i => $p): ?>
        <div class="product-card">
          <a href="/product.php?id=<?= $p['ProductId'] ?>">
            <div class="product-img">
              <img src="<?= placeholderSvg($p['ProductName'], $i) ?>"
                   alt="<?= htmlspecialchars($p['ProductName']) ?>"/>
              <div class="product-overlay">
                <span>Подробнее</span>
              </div>
            </div>
            <div class="product-info">
              <h4><?= htmlspecialchars($p['ProductName']) ?></h4>
              <p class="price"><?= number_format($p['Price'], 0, '.', ' ') ?> ₽</p>
            </div>
          </a>
          <button class="btn-cart-quick" data-id="<?= $p['ProductId'] ?>">
            <i class="bx bx-cart-add"></i> В корзину
          </button>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="section-footer">
      <a href="/catalog.php" class="btn-outline">Смотреть все товары</a>
    </div>
  </div>
</section>

<!-- PROMO BANNER -->
<section class="promo-banner">
  <div class="container">
    <div class="promo-inner">
      <div class="promo-text">
        <span>ОГРАНИЧЕННАЯ КОЛЛЕКЦИЯ</span>
        <h2>ТОЛЬКО ДО КОНЦА СЕЗОНА</h2>
        <p>Скидки до 30% на избранные позиции коллекции</p>
        <a href="/catalog.php" class="btn-primary">Перейти в каталог</a>
      </div>
      <div class="promo-img">
        <img src="<?= placeholderSvg('PROMO', 5) ?>" alt="Акция"/>
      </div>
    </div>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="section featured">
  <div class="container">
    <h2 class="section-title">ВСЯ КОЛЛЕКЦИЯ</h2>
    <div class="products-grid products-grid--wide">
      <?php foreach ($featuredProducts as $i => $p): ?>
      <div class="product-card">
        <a href="/product.php?id=<?= $p['ProductId'] ?>">
          <div class="product-img">
            <img src="<?= placeholderSvg($p['ProductName'], $i+2) ?>"
                 alt="<?= htmlspecialchars($p['ProductName']) ?>"/>
            <div class="product-overlay"><span>Подробнее</span></div>
          </div>
          <div class="product-info">
            <h4><?= htmlspecialchars($p['ProductName']) ?></h4>
            <p class="price"><?= number_format($p['Price'], 0, '.', ' ') ?> ₽</p>
          </div>
        </a>
        <button class="btn-cart-quick" data-id="<?= $p['ProductId'] ?>">
          <i class="bx bx-cart-add"></i> В корзину
        </button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- SUPPORT STRIP -->
<section class="support-strip">
  <div class="container">
    <div class="support-grid">
      <div class="support-item">
        <i class="bx bx-package"></i>
        <div><h4>Бесплатная доставка</h4><p>При заказе от 5000 ₽</p></div>
      </div>
      <div class="support-item">
        <i class="bx bx-refresh"></i>
        <div><h4>Возврат 30 дней</h4><p>Без вопросов</p></div>
      </div>
      <div class="support-item">
        <i class="bx bx-lock-open-alt"></i>
        <div><h4>Безопасная оплата</h4><p>SSL-шифрование</p></div>
      </div>
      <div class="support-item">
        <i class="bx bx-support"></i>
        <div><h4>Поддержка 24/7</h4><p>Онлайн-помощь</p></div>
      </div>
    </div>
  </div>
</section>

<script>
// Быстрое добавление в корзину с главной страницы
document.querySelectorAll('.btn-cart-quick').forEach(btn => {
  btn.addEventListener('click', async () => {
    <?php if (!$isLoggedIn): ?>
      window.location.href = '/login.php?redirect=index.php';
      return;
    <?php endif; ?>
    const id = btn.dataset.id;
    const res = await fetch('/api/cart_add.php', {
      method: 'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({product_id: id, quantity: 1})
    });
    const data = await res.json();
    if (data.success) {
      document.querySelectorAll('.cart-count').forEach(el => el.textContent = data.cart_count);
      btn.innerHTML = '<i class="bx bx-check"></i> Добавлено';
      btn.classList.add('added');
      setTimeout(() => {
        btn.innerHTML = '<i class="bx bx-cart-add"></i> В корзину';
        btn.classList.remove('added');
      }, 2000);
    } else {
      alert(data.error || 'Ошибка');
    }
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
