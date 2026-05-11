<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) { header('Location: /index.php'); exit; }

$errors  = [];
$success = false;
$vals    = ['email'=>'','fullname'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $pass     = $_POST['password']         ?? '';
    $pass2    = $_POST['password_confirm'] ?? '';

    if (!$email)                                 $errors[] = 'Введите email.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email.';
    if (!$fullname)                              $errors[] = 'Введите имя.';
    if (strlen($pass) < 6)                       $errors[] = 'Пароль должен содержать не менее 6 символов.';
    if ($pass !== $pass2)                        $errors[] = 'Пароли не совпадают.';

    if (empty($errors)) {
        $db   = getDb();
        $chk  = $db->prepare("SELECT UserId FROM Users WHERE Email=?");
        $chk->bind_param('s', $email);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $errors[] = 'Этот email уже зарегистрирован.';
        }
        $chk->close();
    }

    if (empty($errors)) {
        $db   = getDb();
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $ins  = $db->prepare("INSERT INTO Users (RoleId,Email,PasswordHash,FullName) VALUES (2,?,?,?)");
        $ins->bind_param('sss', $email, $hash, $fullname);
        if ($ins->execute()) {
            $success = true;
        } else {
            $errors[] = 'Ошибка регистрации. Попробуйте позже.';
        }
        $ins->close();
    }

    $vals = ['email'=>$email, 'fullname'=>$fullname];
}

include __DIR__ . '/includes/header.php';
?>

<main class="auth-main">
  <div class="auth-card">
    <h1>РЕГИСТРАЦИЯ</h1>
    <p class="auth-sub">Создайте аккаунт для оформления заказов</p>

    <?php if ($success): ?>
      <div class="alert alert-success">
        <i class="bx bx-check-circle"></i>
        Регистрация прошла успешно! <a href="/login.php">Войдите</a>
      </div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="alert alert-error">
        <i class="bx bx-error-circle"></i>
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form class="auth-form" method="POST" id="regForm">
      <div class="form-group">
        <label>Имя и фамилия</label>
        <input type="text" name="fullname" placeholder="Иван Иванов"
               value="<?= htmlspecialchars($vals['fullname']) ?>" required/>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="example@email.com"
               value="<?= htmlspecialchars($vals['email']) ?>" required/>
      </div>

      <div class="form-group">
        <label>Пароль</label>
        <div class="input-eye">
          <input type="password" name="password" id="pwd1" placeholder="Минимум 6 символов" required/>
          <button type="button" class="eye-btn" onclick="togglePwd('pwd1',this)">
            <i class="bx bx-hide"></i>
          </button>
        </div>
      </div>

      <div class="form-group">
        <label>Повторите пароль</label>
        <div class="input-eye">
          <input type="password" name="password_confirm" id="pwd2" placeholder="Повторите пароль" required/>
          <button type="button" class="eye-btn" onclick="togglePwd('pwd2',this)">
            <i class="bx bx-hide"></i>
          </button>
        </div>
      </div>

      <div class="form-row">
        <label class="checkbox-label">
          <input type="checkbox" required/> Согласен с условиями использования
        </label>
      </div>

      <button type="submit" class="btn-primary btn-full">Зарегистрироваться</button>
    </form>
    <?php endif; ?>

    <p class="auth-switch">Уже есть аккаунт? <a href="/login.php">Войти</a></p>
  </div>
</main>

<script>
function togglePwd(id, btn) {
  const inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.innerHTML = inp.type === 'password'
    ? '<i class="bx bx-hide"></i>'
    : '<i class="bx bx-show"></i>';
}

// Клиентская проверка совпадения паролей
document.getElementById('regForm')?.addEventListener('submit', (e) => {
  const p1 = document.getElementById('pwd1').value;
  const p2 = document.getElementById('pwd2').value;
  if (p1 !== p2) {
    e.preventDefault();
    alert('Пароли не совпадают');
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
