<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

// Количество товаров в корзине
$cartCount = 0;
if (!empty($_SESSION['user_id'])) {
    $db = getDb();
    $stmt = $db->prepare("SELECT COALESCE(SUM(Quantity),0) FROM CartItems WHERE UserId=?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($cartCount);
    $stmt->fetch();
    $stmt->close();
}

$isLoggedIn = !empty($_SESSION['user_id']);
$isAdmin    = ($isLoggedIn && ($_SESSION['role_id'] ?? 0) == 1);

// Определяем «активную» страницу для подсветки меню
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.4.1/css/glide.core.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.4.1/css/glide.theme.css"/>
  <link rel="stylesheet" href="/css/styles.css"/>
  <title>ГОША РУБЧИНСКИЙ</title>
</head>
<body>
<div class="top-nav">
  <div class="container d-flex">
    <p>Закажите онлайн или позвоните: +7 (800) 555-35-35</p>
    <ul class="d-flex">
      <li><a href="#">О нас</a></li>
      <li><a href="#">FAQ</a></li>
      <li><a href="#">Контакты</a></li>
    </ul>
  </div>
</div>

<nav class="navigation">
  <div class="nav-center container d-flex">
    <a href="/index.php" class="logo"><h1>ГОША РУБЧИНСКИЙ</h1></a>
    <div class="nav-right d-flex">
      <ul class="nav-list d-flex">
        <li class="nav-item"><a href="/index.php"   class="nav-link <?= $currentPage=='index.php'?'active':'' ?>">Домой</a></li>
        <li class="nav-item"><a href="/catalog.php" class="nav-link <?= $currentPage=='catalog.php'?'active':'' ?>">Магазин</a></li>
        <?php if ($isAdmin): ?>
        <li class="nav-item"><a href="/admin/" class="nav-link">Админ</a></li>
        <?php endif; ?>
        <li class="icons d-flex">
          <?php if ($isLoggedIn): ?>
            <a href="/logout.php" class="icon" title="Выйти"><i class="bx bx-log-out"></i></a>
          <?php else: ?>
            <a href="/login.php"  class="icon" title="Войти"><i class="bx bx-user"></i></a>
          <?php endif; ?>
          <a href="/cart.php" class="icon cart-icon" title="Корзина">
            <i class="bx bx-cart"></i>
            <span class="d-flex cart-count"><?= $cartCount ?></span>
          </a>
        </li>
      </ul>
      <div class="icons d-flex">
        <?php if ($isLoggedIn): ?>
          <a href="/logout.php" class="icon" title="Выйти"><i class="bx bx-log-out"></i></a>
        <?php else: ?>
          <a href="/login.php"  class="icon" title="Войти"><i class="bx bx-user"></i></a>
        <?php endif; ?>
        <a href="/cart.php" class="icon cart-icon" title="Корзина">
          <i class="bx bx-cart"></i>
          <span class="d-flex cart-count"><?= $cartCount ?></span>
        </a>
      </div>
    </div>
    <div class="hamburger"><i class="bx bx-menu-alt-left"></i></div>
  </div>
</nav>
