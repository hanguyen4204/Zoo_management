<?php
include "connection.php";

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
function require_login($redirect = 'index.php') {
    if (!is_logged_in()) {
        header("Location: $redirect");
        exit;
    }
}

/* If you want homescreen to be public (no login required), comment out next line */
require_login();
?>

<!doctype html>
<html lang="vi">
<head>
    <title>Thông tin động vật</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap (giữ như cũ để không thay đổi layout bootstrap nếu file gốc dùng) -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <!-- optional JS (giữ nếu cần bootstrap JS) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js" defer></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" defer></script>

    <!-- link chung style -->
    <link rel="stylesheet" href="style.css">
    <style>
      /* nhỏ: đảm bảo thumb-img có style nếu style.css không định nghĩa */
      .thumb-img{ width:80px; height:60px; object-fit:cover; border-radius:6px; }
      .top-nav { margin-bottom: 18px; }
      .btn-logout { padding:8px 12px; border-radius:8px; background:#ef4444; color:#fff; border:none; display:inline-block; }
      .btn-search { background:#3b82f6; color:#fff; border:none; padding:8px 14px; border-radius:8px; }
      .main-panel { padding-top: 6px; }
      .zoo-table thead th { background:#fafafa; }
      .action-btn { padding:6px 10px;border-radius:6px;border:none;color:#fff;display:inline-block;margin-right:6px }
      .btn-edit { background:#f59e0b; } /* amber */
      .btn-delete { background:#ef4444; } /* red */
    </style>
</head>

<body>
<div class="top-nav">
    <div class="container wrap">
        <div class="row">
            <div class="col-xs-6">
                <h1 class="page-title">🐾 Thông tin Động Vật</h1>
            </div>
            <div class="col-xs-6 text-right">
                <!-- show username if logged in -->
                <?php if (is_logged_in()): ?>
                    <span style="margin-right:10px;color:var(--muted)">Xin chào, <strong><?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></span>
                <?php endif; ?>
                <a href="logout.php" class="btn-logout" style="text-decoration:none;">
                    Đăng xuất
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container wrap">

    <div class="main-panel panel">

        <!-- Ô tìm kiếm -->
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-12">
                <form action="" method="post" class="form-inline" role="search">
                    <div class="input-group" style="max-width:560px;">
                        <input type="text" class="form-control" name="search_name"
                               placeholder="Nhập tên động vật..."
                               value="<?php if(isset($_POST['search_name'])) echo htmlspecialchars($_POST['search_name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="input-group-btn">
                            <button class="btn-search" type="submit" name="search">
                                <i class="fa fa-search" aria-hidden="true"></i> Tìm kiếm
                            </button>
                        </span>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bảng danh sách -->
        <div class="table-responsive">
            <table class="table zoo-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 100px;">Ảnh</th>
                        <th>Tên</th>
                        <th>Mô tả</th>
                        <th class="text-center" style="width: 180px;">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                // Giữ nguyên logic gốc: nếu gửi search bằng POST thì filter, đồng thời set cookie last_search
                if (!empty($link)) {
                    if (isset($_POST["search"]) && !empty($_POST["search_name"])) {
                        $search_name = mysqli_real_escape_string($link, $_POST["search_name"]);
                        setcookie("last_search", $search_name, time() + (86400 * 30), "/");
                        $res = mysqli_query($link, "SELECT * FROM table1 WHERE AName LIKE '%$search_name%'");
                    } else {
                        $res = mysqli_query($link, "SELECT * FROM table1");
                    }
                }

                if (!empty($res)) {
                    while($row = mysqli_fetch_array($res)) {
                        echo "<tr>";

                        echo "<td><span class='label label-default'>#" . (int)$row["id"] . "</span></td>";

                        $img_src = !empty($row["photo"]) ? $row["photo"] : 'uploads/default.png';
                        // dùng htmlspecialchars cho đường dẫn và text
                        $img_src_esc = htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8');
                        echo "<td><img src='{$img_src_esc}' class='thumb-img' alt='photo'></td>";

                        echo "<td><strong style='color: var(--green); font-size: 16px;'>" . htmlspecialchars($row["AName"], ENT_QUOTES, 'UTF-8') . "</strong></td>";

                        echo "<td style='color: #666;'>" . htmlspecialchars($row["des"], ENT_QUOTES, 'UTF-8') . "</td>";

                        // Hành động: mọi người đều được Xem; chỉ admin thấy Sửa/Xóa
                        $id = (int)$row["id"];
                        echo "<td class='text-center'>";
                        echo "<a href='view_animal_user.php?id={$id}' class='action-btn' style='background:#06b6d4;text-decoration:none;'>Xem</a>";

                        if (is_admin()) {
                            echo "<a href='edit_animal.php?id={$id}' class='action-btn btn-edit' style='text-decoration:none;'>Sửa</a>";
                            echo "<a href='delete.php?id={$id}' class='action-btn btn-delete' style='text-decoration:none;' onclick=\"return confirm('Xác nhận xóa?')\">Xóa</a>";
                        }

                        echo "</td>";

                        echo "</tr>";
                    }
                } else {
                    // nếu $res rỗng (ví dụ lỗi truy vấn) vẫn hiển thị 1 hàng thông báo
                    echo "<tr><td colspan='5' class='muted' style='padding:14px'>Không có bản ghi.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>

