<footer class="footer" id="terms">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col">
        <h3 class="logo">ГОША РУБЧИНСКИЙ</h3>
        <p>Официальный онлайн-магазин бренда.<br>Москва, Россия.</p>
      </div>
      <div class="footer-col">
        <h4>Навигация</h4>
        <ul>
          <li><a href="/index.php">Главная</a></li>
          <li><a href="/catalog.php">Каталог</a></li>
          <li><a href="/cart.php">Корзина</a></li>
          <li><a href="/login.php">Войти</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Условия</h4>
        <ul>
          <li><a href="#">Доставка</a></li>
          <li><a href="#">Возврат</a></li>
          <li><a href="#">Конфиденциальность</a></li>
          <li><a href="#">Оферта</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Контакты</h4>
        <ul>
          <li><i class="bx bx-phone"></i> +7 (800) 555-35-35</li>
          <li><i class="bx bx-envelope"></i> info@gosha.ru</li>
          <li><i class="bx bxl-instagram"></i> @goshawwt</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> ГОША РУБЧИНСКИЙ. Все права защищены.</p>
    </div>
  </div>
</footer>

<div class="popup hide-popup" id="popup">
  <div class="popup-inner">
    <button class="close-popup" id="closePopup"><i class="bx bx-x"></i></button>
    <h2>НОВАЯ КОЛЛЕКЦИЯ</h2>
    <p>Подпишитесь и получите скидку 10% на первый заказ</p>
    <form class="popup-form" onsubmit="return false;">
      <input type="email" placeholder="Ваш email" required/>
      <button type="submit" class="btn-primary">Подписаться</button>
    </form>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.4.1/glide.min.js"></script>
<script src="/js/index.js"></script>
<script src="/js/slider.js"></script>
</body>
</html>
