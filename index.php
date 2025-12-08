<?php
/* -------------------- SESSION -------------------- */
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 60*60*24,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

require_once "connection1.php";

/* -------------------- AUTO REDIRECT -------------------- */
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? "admin_dashboard.php" : "homescreen.php"));
    exit;
}

$errors = [];

/* -------------------- FORM HANDLE -------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    /* ---------- LOGIN ---------- */
    if ($action === 'login') {
        $email = strtolower(trim($_POST['email']));
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = "Email hoặc mật khẩu không đúng.";
        } else {
            // Login OK – auto detect role
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: " . ($user['role'] === 'admin' ? "admin_dashboard.php" : "homescreen.php"));
            exit;
        }
    }

    /* ---------- SIGNUP (user only) ---------- */
    if ($action === 'signup') {
        $username = trim($_POST['username']);
        $email = strtolower(trim($_POST['email']));
        $password = $_POST['password'];
        $confirm = $_POST['password_confirm'];

        if ($username === "" || $email === "" || $password === "") {
            $errors[] = "Vui lòng điền đầy đủ thông tin.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email không hợp lệ.";
        }
        if ($password !== $confirm) {
            $errors[] = "Mật khẩu xác nhận không khớp.";
        }

        // check email exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = "Email này đã được sử dụng.";
        }

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // FORCE role = user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $email, $hash]);

            $uid = $pdo->lastInsertId();

            session_regenerate_id(true);
            $_SESSION['user_id'] = $uid;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';

            header("Location: homescreen.php");
            exit;
        }
    }
}
?>

<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8"/>
<title>Zoo Management - Login</title>

<style>
    :root{--green:#2f7a2f;--bg:#f4f6f4}
    body{
        background:var(--bg);
        display:flex;
        height:100vh;
        align-items:center;
        justify-content:center;
        font-family:Arial;
    }
    .box{
        background:#fff;width:380px;padding:24px;border-radius:12px;
        box-shadow:0 6px 18px rgba(0,0,0,0.1);
    }
    .tabs{display:flex;margin-bottom:18px}
    .tab{
        flex:1;text-align:center;padding:10px;font-weight:bold;
        cursor:pointer;background:#eee;border-radius:6px
    }
    .tab.active{background:var(--green);color:#fff}
    .error{background:#ffd6d6;color:#b30000;padding:10px;border-radius:6px;margin-bottom:10px}
    label{margin-top:10px;display:block;font-weight:bold}
    input{width:100%;padding:10px;margin-top:5px;border-radius:6px;border:1px solid #ccc}
    button{
        width:100%;margin-top:15px;padding:10px;background:var(--green);
        color:white;border:0;border-radius:6px;font-size:16px;cursor:pointer
    }
</style>
</head>
<body>

<div class="box">

<?php if ($errors): ?>
<div class="error">
    <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
</div>
<?php endif; ?>

<div class="tabs">
    <div id="tab-login" class="tab active">Đăng nhập</div>
    <div id="tab-signup" class="tab">Đăng ký (User)</div>
</div>

<!-- LOGIN -->
<div id="panel-login">
<form method="POST">
    <input type="hidden" name="action" value="login">

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Mật khẩu</label>
    <input type="password" name="password" required>

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

    <button>Đăng ký</button>
</form>
</div>

</div>

<script>
const tabLogin=document.getElementById("tab-login");
const tabSignup=document.getElementById("tab-signup");
const panelLogin=document.getElementById("panel-login");
const panelSignup=document.getElementById("panel-signup");

tabLogin.onclick=()=>{
    tabLogin.classList.add("active");
    tabSignup.classList.remove("active");
    panelLogin.style.display="block";
    panelSignup.style.display="none";
};

tabSignup.onclick=()=>{
    tabSignup.classList.add("active");
    tabLogin.classList.remove("active");
    panelSignup.style.display="block";
    panelLogin.style.display="none";
};
</script>

</body>
</html>
