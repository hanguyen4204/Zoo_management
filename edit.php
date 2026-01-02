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
function require_role($role) {
    if (!is_logged_in()) {
        header("Location: index.php");
        exit;
    }
    if ($role === 'admin' && !is_admin()) {
        http_response_code(403);
        echo "403 Forbidden — Bạn không có quyền truy cập.";
        exit;
    }
}

/* Chỉ admin mới được chỉnh */
require_role('admin');

/* Lấy id an toàn */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: homescreen.php");
    exit;
}

/* Khởi tạo biến hiển thị */
$firstname = $lastname = $area = $contact = $Des = "";
$photoExisting = "";

/* Lấy dữ liệu bản ghi từ DB (prepared statement) */
if ($stmt = mysqli_prepare($link, "SELECT AName, Species, Area, Date, des, photo FROM table1 WHERE id = ?")) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $AName_db, $Species_db, $Area_db, $Date_db, $des_db, $photo_db);
    if (mysqli_stmt_fetch($stmt)) {
        $firstname = $AName_db;
        $lastname  = $Species_db;
        $area      = $Area_db;
        $contact   = $Date_db;
        $Des       = $des_db;
        $photoExisting = $photo_db;
    } else {
        // không tìm thấy -> quay về danh sách
        mysqli_stmt_close($stmt);
        header("Location: homescreen.php");
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    // lỗi prepare
    header("Location: homescreen.php");
    exit;
}

/* Xử lý submit update */
if (isset($_POST["update"])) {
    // Lấy dữ liệu từ form, trim để an toàn hơn
    $AName = trim($_POST['AName'] ?? '');
    $Species = trim($_POST['Species'] ?? '');
    $Area = trim($_POST['Area'] ?? '');
    $Date = trim($_POST['Date'] ?? '');
    $des = trim($_POST['des'] ?? '');

    // Basic validation (bạn có thể mở rộng)
    $errors = [];
    if ($AName === '') $errors[] = "Tên không được để trống.";

    // Handle file upload if provided
    $photoPath = null; // nếu có file mới, sẽ lưu đường dẫn relative
    if (!empty($_FILES['photo']['name']) && isset($_FILES['photo'])) {
        $f = $_FILES['photo'];
        if ($f['error'] === UPLOAD_ERR_OK) {
            $check = @getimagesize($f['tmp_name']);
            if ($check === false) {
                $errors[] = "Tập tin tải lên không phải ảnh.";
            } elseif ($f['size'] > 5 * 1024 * 1024) {
                $errors[] = "Ảnh quá lớn (tối đa 5MB).";
            } else {
                $uploadDir = __DIR__ . '/uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $basename = bin2hex(random_bytes(8));
                $newName = $basename . ($ext ? '.' . $ext : '');
                $target = $uploadDir . '/' . $newName;
                if (!move_uploaded_file($f['tmp_name'], $target)) {
                    $errors[] = "Không lưu được file ảnh.";
                } else {
                    $photoPath = 'uploads/' . $newName; // relative path để lưu vào DB
                }
            }
        } elseif ($f['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors[] = "Lỗi khi upload file (code: {$f['error']}).";
        }
    }

    if (empty($errors)) {
        // Nếu có ảnh mới, cập nhật cả photo; nếu không, cập nhật các trường khác
        if ($photoPath !== null) {
            $sql = "UPDATE table1 SET AName = ?, Species = ?, Area = ?, Date = ?, des = ?, photo = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssssssi", $AName, $Species, $Area, $Date, $des, $photoPath, $id);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } else {
                $ok = false;
            }
        } else {
            $sql = "UPDATE table1 SET AName = ?, Species = ?, Area = ?, Date = ?, des = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssssi", $AName, $Species, $Area, $Date, $des, $id);
                $ok = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } else {
                $ok = false;
            }
        }

        if (!empty($ok)) {
            // Cập nhật thành công -> chuyển về view
            header("Location: view_animal.php?id=" . $id . "&msg=success");
            exit;
        } else {
            $errors[] = "Cập nhật thất bại: " . mysqli_error($link);
        }
    }
    // Nếu có lỗi, các biến sẽ được giữ và hiển thị lại form với thông báo
}
?>

<!doctype html>
<html lang="vi">
<head>
    <title>Chỉnh sửa thông tin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- giữ bootstrap như cũ -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .thumb-preview { width:160px; height:120px; object-fit:cover; border-radius:6px; display:block; margin-bottom:8px; }
        .panel { padding:18px; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,0.06); background:#fff; }
    </style>
</head>
<body>
<div class="container" style="margin-top:18px;">
    <div class="col-lg-6 col-md-8 col-sm-10 col-xs-12 panel">
        <h2>Chỉnh sửa thông tin</h2>

        <?php
        if (!empty($errors)) {
            echo "<div class='alert alert-danger'><ul style='margin:0;padding-left:18px;'>";
            foreach ($errors as $er) {
                echo "<li>" . htmlspecialchars($er, ENT_QUOTES, 'UTF-8') . "</li>";
            }
            echo "</ul></div>";
        }
        ?>

        <form action="" name="form1" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="firstname">Tên:</label>
                <input type="text" class="form-control" required id="firstname" name="AName" value="<?= htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="Species">Giống loài:</label>
                <input type="text" class="form-control" required id="Species" name="Species" value="<?= htmlspecialchars($lastname, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="Area">Khu vực:</label>
                <input type="text" class="form-control" required id="Area" name="Area" value="<?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="Date">Ngày sinh:</label>
                <input type="date" class="form-control" id="Date" name="Date" value="<?= htmlspecialchars($contact, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-group">
                <label for="Des">Mô tả:</label>
                <input type="text" class="form-control" id="Des" name="des" value="<?= htmlspecialchars($Des, ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label>Ảnh hiện tại:</label><br>
                <?php
                $preview = $photoExisting ?: 'uploads/default.png';
                echo "<img src='" . htmlspecialchars($preview, ENT_QUOTES, 'UTF-8') . "' class='thumb-preview' alt='preview'>";
                ?>
                <label for="photo">Tải ảnh mới (tùy chọn):</label>
                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
            </div>

            <div style="display:flex;gap:8px">
                <button type="submit" name="update" class="btn btn-primary">Cập nhật</button>
                <a href="view_animal.php?id=<?= $id ?>" class="btn btn-default">Quay lại</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
