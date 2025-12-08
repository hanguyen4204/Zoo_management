<?php
include "connection.php";

// Kiểm tra tham số ID
if (!isset($_GET['id'])) {
    echo "Lỗi: Không có ID.";
    exit;
}

$id = intval($_GET['id']);

// Lấy dữ liệu động vật theo ID
$sql = "SELECT * FROM table1 WHERE id = $id";
$res = mysqli_query($link, $sql);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    echo "Không tìm thấy dữ liệu.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Thông tin chi tiết động vật</title>
    <link rel="stylesheet" 
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        .detail-box {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 10px;
            background: #fafafa;
        }
        .detail-label {
            font-weight: bold;
            color: #333;
        }
        .profile-img {
            width: 100%;
            max-width: 250px;
            height: auto;
            border-radius: 10px;
            margin-bottom: 20px;
            object-fit: cover;
        }
    </style>
</head>

<body>

<div class="container mt-5">

    <h2 class="mb-4">Thông tin chi tiết động vật</h2>

    <div class="row">

        <!-- Ảnh -->
        <div class="col-md-4">
            <img src="<?php echo $data['photo']; ?>" class="profile-img">
        </div>

        <!-- Thông tin -->
        <div class="col-md-8 detail-box">
            <p><span class="detail-label">ID:</span> <?php echo $data['id']; ?></p>
            <p><span class="detail-label">Tên:</span> <?php echo $data['AName']; ?></p>
            <p><span class="detail-label">Giống loài:</span> <?php echo $data['Species']; ?></p>
            <p><span class="detail-label">Khu vực:</span> <?php echo $data['Area']; ?></p>
            <p><span class="detail-label">Ngày sinh:</span> <?php echo $data['Date']; ?></p>
            <p><span class="detail-label">Mô tả:</span> <?php echo $data['des']; ?></p>

            <a href="homescreen.php" class="btn btn-primary mt-3">Quay lại</a>
        </div>

    </div>
</div>

</body>
</html>
