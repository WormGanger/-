<?php
require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) { header('Location: /index.php'); exit; }

$error    = '';
$redirect = htmlspecialchars($_GET['redirect'] ?? 'index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$email || !$pass) {
        $error = 'Заполните все поля.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email.';
    } else {
        $db   = getDb();
        $stmt = $db->prepare("SELECT UserId, RoleId, PasswordHash, FullName FROM Users WHERE Email=?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($pass, $user['PasswordHash'])) {
            $error = 'Неверный email или пароль.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['UserId'];
            $_SESSION['role_id']  = $user['RoleId'];
            $_SESSION['fullname'] = $user['FullName'];
            header('Location: /' . $redirect); exit;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="auth-main">
  <div class="auth-card">
    <h1>ВОЙТИ</h1>
    <p class="auth-sub">Введите данные вашего аккаунта</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><i class="bx bx-error-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form class="auth-form" method="POST">
      <input type="hidden" name="redirect" value="<?= $redirect ?>"/>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="example@email.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
      </div>

      <div class="form-group">
        <label>Пароль</label>
        <div class="input-eye">
          <input type="password" name="password" id="pwdInput" placeholder="••••••••" required/>
          <button type="button" class="eye-btn" onclick="togglePwd('pwdInput',this)">
            <i class="bx bx-hide"></i>
          </button>
        </div>
      </div>

      <div class="form-row">
        <label class="checkbox-label">
          <input type="checkbox" name="remember"/> Запомнить меня
        </label>
        <a href="#" class="link-muted">Забыли пароль?</a>
      </div>

      <button type="submit" class="btn-primary btn-full">Войти</button>
    </form>

    <p class="auth-switch">Нет аккаунта? <a href="/register.php">Зарегистрироваться</a></p>

    <div class="demo-hint">
      <p>Демо-доступ: <code>admin@gosha.ru</code> / <code>password</code></p>
    </div>
  </div>
</main>

<script>
function togglePwd(id, btn) {
  const inp = document.getElementById(id);
  if (inp.type === 'password') {
    inp.type = 'text';
    btn.innerHTML = '<i class="bx bx-show"></i>';
  } else {
    inp.type = 'password';
    btn.innerHTML = '<i class="bx bx-hide"></i>';
  }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
