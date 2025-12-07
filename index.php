<?php
// Cấu hình Session Cookie
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
require_once 'connection1.php';

// Nếu đã có session thì chuyển đúng dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: homescreen.php");
    }
    exit;
}

// Auto Login bằng Cookie
if (isset($_COOKIE['remember_user'])) {
    $cookie = explode(':', $_COOKIE['remember_user']);
    if (count($cookie) === 2) {
        $uid = $cookie[0];
        $token = $cookie[1];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch();

        if ($user && hash_equals($user['remember_token'], $token)) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: homescreen.php");
            }
            exit;
        }
    }
}

$errors = [];
$success = "";
$csrf_token = $_SESSION['csrf_token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ---------------- SIGNUP ----------------
    if ($action === 'signup') {
        $username = trim($_POST['username']);
        $email = strtolower(trim($_POST['email']));
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        $role = $_POST['role']; // USER hoặc ADMIN

        if ($username === '' || $email === '' || $password === '') {
            $errors[] = "Vui lòng điền đầy đủ thông tin.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email không hợp lệ.";
        }
        if ($password !== $password_confirm) {
            $errors[] = "Mật khẩu xác nhận không khớp.";
        }
        if (strlen($password) < 6) {
            $errors[] = "Mật khẩu phải tối thiểu 6 ký tự.";
        }

        if (empty($errors)) {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);

            if ($check->fetch()) {
                $errors[] = "Email đã được sử dụng.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hash, $role]);

                $newUserId = $pdo->lastInsertId();

                session_regenerate_id(true);
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;

                if ($role === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: homescreen.php");
                }
                exit;
            }
        }

    // ---------------- LOGIN ----------------
    } elseif ($action === 'login') {
        $email = strtolower(trim($_POST['email']));
        $password = $_POST['password'];
        $selected_role = $_POST['selected_role'] ?? 'user';
        $remember = isset($_POST['remember']);

        if ($email === '' || $password === '') {
            $errors[] = "Vui lòng điền email và mật khẩu.";
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = "Email hoặc mật khẩu không đúng.";
        } else {
            // Kiểm tra role có đúng không
            if ($selected_role !== $user['role']) {
                $errors[] = "Bạn không có quyền đăng nhập với vai trò này.";
            } else {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Remember Me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $pdo->prepare("UPDATE users SET remember_token=? WHERE id=?")
                        ->execute([$token, $user['id']]);

                    setcookie(
                        "remember_user",
                        $user['id'] . ":" . $token,
                        time() + (86400 * 30),
                        "/",
                        "",
                        $secure,
                        true
                    );
                }

                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: homescreen.php");
                }
                exit;
            }
        }
    }
}

// Giao diện
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Zoo Management - Login</title>
  <style>
    :root{--green:#2f7a2f;--bg:#f4f6f4;--card:#fff;--muted:#6b7280;--danger:#d23b3b}
    body{background:var(--bg);display:flex;height:100vh;align-items:center;justify-content:center;font-family:Arial}
    .box{background:#fff;width:380px;padding:24px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,0.1);}
    .tabs{display:flex;margin-bottom:18px}
    .tab{flex:1;text-align:center;padding:10px;font-weight:bold;cursor:pointer;background:#eee;border-radius:6px}
    .tab.active{background:var(--green);color:#fff}
    .msg{padding:12px;margin-bottom:12px;border-radius:8px}
    .error{background:#ffd6d6;color:#a70000;}
    label{font-weight:bold;margin-top:10px;display:block}
    input,select{width:100%;padding:10px;margin-top:6px;border-radius:6px;border:1px solid #ccc}
    button{width:100%;margin-top:14px;padding:10px;background:var(--green);color:#fff;border:0;border-radius:6px;font-size:15px;cursor:pointer}
  </style>
</head>
<body>

<div class="box">

<?php if ($errors): ?>
<div class="msg error">
    <ul>
        <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach ?>
    </ul>
</div>
<?php endif; ?>

<div class="tabs">
  <div id="tab-login" class="tab">Đăng nhập</div>
  <div id="tab-signup" class="tab">Đăng ký</div>
</div>

<!-- LOGIN -->
<div id="panel-login">
<form method="POST">
    <input type="hidden" name="action" value="login">

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Mật khẩu</label>
    <input type="password" name="password" required>

    <label>Đăng nhập với vai trò:</label>
    <select name="selected_role" required>
        <option value="user">Người dùng</option>
        <option value="admin">Quản trị viên</option>
    </select>

    <label><input type="checkbox" name="remember"> Ghi nhớ đăng nhập</label>

    <button>Đăng nhập</button>
</form>
</div>

<!-- SIGNUP -->
<div id="panel-signup" style="display:none">
<form method="POST">
    <input type="hidden" name="action" value="signup">

    <label>Tên người dùng</label>
    <input type="text" name="username" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Mật khẩu</label>
    <input type="password" name="password" required>

    <label>Xác nhận mật khẩu</label>
    <input type="password" name="password_confirm" required>

    <label>Đăng ký vai trò:</label>
    <select name="role" required>
        <option value="user">Người dùng</option>
        <option value="admin">Quản trị viên</option>
    </select>

    <button>Đăng ký</button>
</form>
</div>

</div>

<script>
const tabLogin=document.getElementById("tab-login");
const tabSignup=document.getElementById("tab-signup");
const panelLogin=document.getElementById("panel-login");
const panelSignup=document.getElementById("panel-signup");

tabLogin.onclick = ()=>{
    tabLogin.classList.add("active");
    tabSignup.classList.remove("active");
    panelLogin.style.display="block";
    panelSignup.style.display="none";
};
tabSignup.onclick = ()=>{
    tabSignup.classList.add("active");
    tabLogin.classList.remove("active");
    panelSignup.style.display="block";
    panelLogin.style.display="none";
};

tabLogin.classList.add("active");
</script>

</body>
</html>
