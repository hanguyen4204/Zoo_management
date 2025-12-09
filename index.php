<?php

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
  'lifetime' => 60*60*24,
  'path' => '/',
  'domain' => '',
  'secure' => $secure,
  'httponly' => true,
  'samesite' => 'Lax'
]);
session_start();

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


if (session_status() === PHP_SESSION_NONE) session_start();

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function current_role() {
    return $_SESSION['role'] ?? null;
}

function is_admin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function is_user() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'user');
}

function deny($msg = 'Bạn không có quyền truy cập.') {
    http_response_code(403);
    echo $msg;
    exit;
}

/**
 * require_login() - chuyển hướng về trang login (mặc định index.php) nếu chưa login
 * Sử dụng ở những trang cần login bắt buộc (ví dụ homescreen.php)
 */
function require_login($redirect = 'index.php') {
    if (!is_logged_in()) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * require_role($role) - đảm bảo user có role tương ứng (vd 'admin')
 * Nếu không hợp lệ -> deny()
 */
function require_role($role) {
    if (!is_logged_in()) deny('Vui lòng đăng nhập.');
    if ($role === 'admin' && !is_admin()) deny('Chỉ admin mới được truy cập.');
    if ($role === 'user' && !is_user()) deny('Chỉ user mới được truy cập.');
}

/**
 * require_any_role(['admin','user'])
 */
function require_any_role(array $roles) {
    if (!is_logged_in()) deny('Vui lòng đăng nhập.');
    if (!in_array(current_role(), $roles, true)) deny('Bạn không có quyền.');
}

/**
 * check_session_timeout($maxIdleSeconds)
 * - optional: gọi trong pages để auto logout khi idle
 */
function check_session_timeout($maxIdle = 1800) {
    if (!isset($_SESSION['last_active'])) {
        $_SESSION['last_active'] = time();
        return;
    }
    if (time() - $_SESSION['last_active'] > $maxIdle) {
        session_unset();
        session_destroy();
        header("Location: index.php?timeout=1");
        exit;
    }
    $_SESSION['last_active'] = time();
}

require_once 'connection1.php';

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $errors[] = "Yêu cầu không hợp lệ (CSRF).";
  } else {
    $action = $_POST['action'] ?? '';

    if ($action === 'signup') {
      $username = trim($_POST['username'] ?? '');
      $email = strtolower(trim($_POST['email'] ?? ''));
      $password = $_POST['password'] ?? '';
      $password_confirm = $_POST['password_confirm'] ?? '';

      if ($username === '' || $email === '' || $password === '') {
        $errors[] = "Vui lòng điền đủ thông tin.";
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ.";
      } elseif ($password !== $password_confirm) {
        $errors[] = "Mật khẩu xác nhận không khớp.";
      } elseif (strlen($password) < 6) {
        $errors[] = "Mật khẩu quá ngắn (tối thiểu 6 ký tự).";
      }

      if (empty($errors)) {
        try {
          $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
          $stmt->execute([$email]);
          if ($stmt->fetch()) {
            $errors[] = "Email đã được sử dụng.";
          } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hash]);

            $newUserId = $pdo->lastInsertId();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['username'] = $username;
            $_SESSION['last_active'] = time();

            header("Location: index.php");
            exit;
          }
        } catch (PDOException $e) {
          $errors[] = "Lỗi hệ thống, vui lòng thử lại sau.";
        }
      }

    } elseif ($action === 'login') {
      $email = strtolower(trim($_POST['email'] ?? ''));
      $password = $_POST['password'] ?? '';

      if ($email === '' || $password === '') {
        $errors[] = "Vui lòng điền email và mật khẩu.";
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ.";
      }

      if (empty($errors)) {
        try {
          $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
          $stmt->execute([$email]);
          $user = $stmt->fetch();

          if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_active'] = time();

            header("Location: homescreen.php");
            exit;
          } else {
            $errors[] = "Email hoặc mật khẩu không đúng.";
          }
        } catch (PDOException $e) {
          $errors[] = "Lỗi hệ thống, vui lòng thử lại sau.";
        }
      }

    } else {
      $errors[] = "Hành động không hợp lệ.";
    }
  }
}

$openTab = 'login';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {
  $openTab = 'signup';
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Zoo Auth</title>
  <style>
    :root{--green:#2f7a2f;--bg:#f4f6f4;--card:#fff;--muted:#6b7280;--danger:#d23b3b}
    *{box-sizing:border-box}
    html,body{height:100%;margin:0;font-family:Inter,system-ui,Arial,sans-serif}
    
    body{
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:center;
      background:var(--bg);
      padding:20px;
    }

    /* Welcome */
    .welcome{text-align:center;margin-bottom:14px}
    .welcome h1{margin:0;font-size:28px;color:var(--green)}
    .welcome p{margin:6px 0 0;color:var(--muted)}

    /* Auth card */
    .auth-box{
      width:100%;
      max-width:420px;
      background:var(--card);
      padding:26px;
      border-radius:12px;
      box-shadow:0 10px 26px rgba(0,0,0,0.10);
    }

    .tabs{
      display:flex;background:#f3f4f5;border-radius:8px;overflow:hidden;margin-bottom:16px
    }
    .tab{flex:1;padding:10px 0;text-align:center;font-weight:600;color:#444;cursor:pointer}
    .tab.active{background:var(--green);color:#fff}

    h2{margin:0 0 10px;color:var(--green)}

    label{display:block;margin:10px 0 6px;font-weight:600;color:#111}
    input[type=email],input[type=text],input[type=password]{
      width:100%;padding:10px;border:1px solid #ddd;border-radius:8px
    }
    .btn{
      width:100%;margin-top:14px;padding:10px;border-radius:8px;
      background:var(--green);color:#fff;border:0;font-weight:600;cursor:pointer
    }
    .muted{color:var(--muted);font-size:0.9rem;margin-top:8px}

    .msg{padding:12px;border-radius:8px;margin-bottom:12px;font-size:0.95rem}
    .error{background:#fff5f5;color:var(--danger);border:1px solid rgba(210,59,59,0.15)}
    .success{background:#eef8ef;color:var(--green);border:1px solid rgba(47,122,47,0.15)}

    ul.err-list{margin:0;padding-left:18px}
  </style>
</head>
<body>

  <div class="welcome">
    <h1>Welcome to Zoo Management</h1>
    <p>Đăng nhập hoặc đăng ký để tiếp tục</p>
  </div>

  <div class="auth-box">
    
    <?php if (!empty($errors)): ?>
      <div class="msg error">
        <ul class="err-list">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="msg success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="tabs">
      <div id="tab-login" class="tab">Đăng nhập</div>
      <div id="tab-signup" class="tab">Đăng ký</div>
    </div>

    <!-- LOGIN -->
    <div id="panel-login">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="action" value="login">

        <label>Email</label>
        <input type="email" name="email" placeholder="you@example.com" required>

        <label>Mật khẩu</label>
        <input type="password" name="password" placeholder="Mật khẩu" required>

        <button class="btn">Đăng nhập</button>
      </form>
      <div class="muted">Chưa có tài khoản? <a href="#" onclick="activateTab('signup');return false">Đăng ký</a></div>
    </div>

    <!-- SIGNUP -->
    <div id="panel-signup" style="display:none">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="action" value="signup">

        <label>Tên người dùng</label>
        <input type="text" name="username" placeholder="Tên hiển thị" required>

        <label>Email</label>
        <input type="email" name="email" placeholder="you@example.com" required>

        <label>Mật khẩu</label>
        <input type="password" name="password" placeholder="Tối thiểu 6 ký tự" required>

        <label>Nhập lại mật khẩu</label>
        <input type="password" name="password_confirm" placeholder="Xác nhận mật khẩu" required>

        <button class="btn">Đăng ký</button>
      </form>
      <div class="muted">Đã có tài khoản? <a href="#" onclick="activateTab('login');return false">Đăng nhập</a></div>
    </div>

  </div>

<script>
  const tabLogin = document.getElementById("tab-login");
  const tabSignup = document.getElementById("tab-signup");
  const panelLogin = document.getElementById("panel-login");
  const panelSignup = document.getElementById("panel-signup");

  function activateTab(tab){
    if(tab === "login"){
      tabLogin.classList.add("active");
      tabSignup.classList.remove("active");
      panelLogin.style.display = "block";
      panelSignup.style.display = "none";
    } else {
      tabSignup.classList.add("active");
      tabLogin.classList.remove("active");
      panelSignup.style.display = "block";
      panelLogin.style.display = "none";
    }
  }

  activateTab(<?= json_encode($openTab) ?>);
</script>

</body>
</html>


