<?php
require_once __DIR__ . '/config/db.php';
$db = getDb();

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort       = $_GET['sort'] ?? 'default';
$search     = trim($_GET['q'] ?? '');

// Все категории для фильтра
$categories = $db->query("SELECT * FROM Categories ORDER BY CategoryId")->fetch_all(MYSQLI_ASSOC);

// Сортировка
$orderBy = match ($sort) {
    'price_asc'  => 'p.Price ASC',
    'price_desc' => 'p.Price DESC',
    'name_asc'   => 'p.ProductName ASC',
    default      => 'p.ProductId ASC',
};

// Запрос товаров с фильтрацией
$where  = ['p.IsActive = 1'];
$params = [];
$types  = '';

if ($categoryId > 0) {
    $where[]  = 'p.CategoryId = ?';
    $params[] = $categoryId;
    $types   .= 'i';
}
if ($search !== '') {
    $where[]  = 'p.ProductName LIKE ?';
    $params[] = "%$search%";
    $types   .= 's';
}

$sql = "SELECT p.*, pi.ImagePath, c.CategoryName
        FROM Products p
        LEFT JOIN ProductImages pi ON pi.ProductId=p.ProductId AND pi.IsMain=1
        JOIN Categories c ON c.CategoryId=p.CategoryId
        WHERE " . implode(' AND ', $where) . "
        ORDER BY $orderBy";

$stmt = $db->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <h1>КАТАЛОГ</h1>
    <p>Коллекция ГОША РУБЧИНСКИЙ</p>
  </div>
</div>

<main class="catalog-main">
  <div class="container catalog-layout">

    <!-- САЙДБАР -->
    <aside class="catalog-sidebar">
      <div class="filter-block">
        <h3>Категории</h3>
        <ul>
          <li>
            <a href="/catalog.php<?= $sort!='default'?"?sort=$sort":'' ?>"
               class="<?= $categoryId==0?'active':'' ?>">Все товары</a>
          </li>
          <?php foreach ($categories as $cat): ?>
          <li>
            <a href="/catalog.php?category=<?= $cat['CategoryId'] ?><?= $sort!='default'?"&sort=$sort":'' ?>"
               class="<?= $categoryId==$cat['CategoryId']?'active':'' ?>">
              <?= htmlspecialchars($cat['CategoryName']) ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="filter-block">
        <h3>Поиск</h3>
        <form method="GET" action="/catalog.php">
          <?php if ($categoryId): ?>
            <input type="hidden" name="category" value="<?= $categoryId ?>"/>
          <?php endif; ?>
          <input type="text" name="q" placeholder="Название товара..."
                 value="<?= htmlspecialchars($search) ?>" class="search-input"/>
          <button type="submit" class="btn-search"><i class="bx bx-search"></i></button>
        </form>
      </div>
    </aside>

    <!-- ТОВАРЫ -->
    <div class="catalog-content">
      <div class="catalog-toolbar">
        <p class="catalog-count">Найдено товаров: <strong><?= count($products) ?></strong></p>
        <div class="catalog-sort">
          <label>Сортировка:</label>
          <select onchange="location.href=this.value">
            <?php
            $base = '/catalog.php?' . ($categoryId?"category=$categoryId&":'') . ($search?"q=".urlencode($search)."&":'');
            $sortOptions = [
                'default'    => 'По умолчанию',
                'price_asc'  => 'Цена: по возрастанию',
                'price_desc' => 'Цена: по убыванию',
                'name_asc'   => 'По названию',
            ];
            foreach ($sortOptions as $val => $label):
            ?>
            <option value="<?= $base ?>sort=<?= $val ?>" <?= $sort==$val?'selected':'' ?>>
              <?= $label ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <?php if (empty($products)): ?>
        <div class="empty-state">
          <i class="bx bx-search-alt"></i>
          <h3>Товары не найдены</h3>
          <p>Попробуйте изменить параметры фильтра</p>
          <a href="/catalog.php" class="btn-outline">Сбросить фильтр</a>
        </div>
      <?php else: ?>
        <div class="products-grid">
          <?php foreach ($products as $i => $p): ?>
          <div class="product-card">
            <a href="/product.php?id=<?= $p['ProductId'] ?>">
              <div class="product-img">
                <img src="<?= placeholderSvg($p['ProductName'], $i) ?>"
                     alt="<?= htmlspecialchars($p['ProductName']) ?>"/>
                <div class="product-overlay"><span>Подробнее</span></div>
              </div>
              <div class="product-info">
                <span class="product-cat"><?= htmlspecialchars($p['CategoryName']) ?></span>
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
      <?php endif; ?>
    </div>
  </div>
</main>

<script>
document.querySelectorAll('.btn-cart-quick').forEach(btn => {
  btn.addEventListener('click', async () => {
    <?php if (!$isLoggedIn): ?>
      window.location.href = '/login.php?redirect=catalog.php';
      return;
    <?php endif; ?>
    const res = await fetch('/api/cart_add.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({product_id: btn.dataset.id, quantity:1})
    });
    const data = await res.json();
    if (data.success) {
      document.querySelectorAll('.cart-count').forEach(el => el.textContent = data.cart_count);
      btn.innerHTML = '<i class="bx bx-check"></i> Добавлено';
      btn.classList.add('added');
      setTimeout(()=>{
        btn.innerHTML='<i class="bx bx-cart-add"></i> В корзину';
        btn.classList.remove('added');
      }, 2000);
    }
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
