<?php
include "connection.php";

$message = ""; // Biến lưu thông báo thành công/thất bại

// 1. XỬ LÝ KHI NGƯỜI DÙNG NHẤN NÚT "THÊM MỚI"
if (isset($_POST["submit"])) {
    
    // Lấy dữ liệu từ form
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $desc = mysqli_real_escape_string($link, $_POST['description']);
    $time = $_POST['time']; // Định dạng từ input là: 2026-05-02T09:30
    $zone_id = $_POST['zone_id'];
    $file_link = mysqli_real_escape_string($link, $_POST['file_link']);

    // Xử lý Upload Ảnh
    $imagePath = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        // Tạo tên file ngẫu nhiên để tránh trùng (time + tên gốc)
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $fileName;
        
        // Di chuyển file từ bộ nhớ tạm vào thư mục uploads
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $imagePath = $target_file;
        } else {
            $message = "<div class='alert alert-danger'>Lỗi: Không thể upload ảnh.</div>";
        }
    }

    // Insert vào Database
    // Lưu ý: Cột 'image' lưu đường dẫn (vd: uploads/123_abc.jpg)
    $sql = "INSERT INTO activity (name, description, time, zone_id, file_link, image) 
            VALUES ('$name', '$desc', '$time', '$zone_id', '$file_link', '$imagePath')";

    if (mysqli_query($link, $sql)) {
        $message = "<div class='alert alert-success'>✅ Đã thêm hoạt động thành công!</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Lỗi Database: " . mysqli_error($link) . "</div>";
    }
}

// 2. LẤY DANH SÁCH KHU VỰC (ZONE) ĐỂ HIỆN SELECT BOX
$zone_query = mysqli_query($link, "SELECT * FROM zones");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Thêm Hoạt Động</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; padding-top: 50px; }
        .form-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .form-header { text-align: center; margin-bottom: 30px; color: #0b3d2e; }
        label { font-weight: 600; color: #333; margin-top: 10px; }
        .btn-custom {
            background: #0b3d2e; color: #fff; width: 100%; padding: 12px;
            font-size: 16px; font-weight: bold; border: none; margin-top: 20px;
            transition: 0.3s;
        }
        .btn-custom:hover { background: #f4f91d; color: #0b3d2e; }
        .note { font-size: 12px; color: #777; font-style: italic; }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <h2 class="form-header">➕ Thêm Hoạt Động Mới</h2>
        
        <?= $message ?>

        <form action="" method="post" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Tên hoạt động:</label>
                <input type="text" name="name" class="form-control" placeholder="Ví dụ: Xiếc Cá Heo" required>
            </div>

            <div class="form-group">
                <label>Mô tả ngắn:</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Mô tả hiển thị bên ngoài thẻ..."></textarea>
            </div>

            <div class="form-group">
                <label>Thời gian:</label>
                <input type="datetime-local" name="time" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Khu vực diễn ra:</label>
                <select name="zone_id" class="form-control" required>
                    <option value="">-- Chọn khu vực --</option>
                    <?php while($z = mysqli_fetch_array($zone_query)): ?>
                        <option value="<?= $z['id'] ?>">
                            <?= $z['zone_name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Link File Riêng (Tên file PHP):</label>
                <input type="text" name="file_link" class="form-control" placeholder="Ví dụ: activity_dolphin.php">
                <p class="note">Lưu ý: Bạn phải tạo file này thủ công trong code sau khi thêm.</p>
            </div>

            <div class="form-group">
                <label>Ảnh đại diện (Thumbnail):</label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
            </div>

            <button type="submit" name="submit" class="btn btn-custom">THÊM HOẠT ĐỘNG</button>
            
            <div class="text-center" style="margin-top: 15px;">
                <a href="admin_dashboard.php">← Quay lại </a>
            </div>

        </form>
    </div>
</div>

</body>
</html>