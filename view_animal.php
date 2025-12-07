<?php
include "connection.php";
// Kiểm tra ID
if (!isset($_GET['id'])) {
    echo "Lỗi: Không có ID.";
    exit;
}

$id = $_GET['id'];

// Xử lý Update
if (isset($_POST['update'])) {

    $firstname = $_POST['AName'];
    $lastname  = $_POST['Species'];
    $email     = $_POST['Area'];
    $contact   = $_POST['Date'];
    $des      = $_POST['des'];

    $update_sql = "
        UPDATE table1 SET 
        AName='$firstname',
        Species='$lastname',
        Area='$email',
        Date='$contact'
        des='$des'

        WHERE id=$id
    ";

    mysqli_query($link, $update_sql);
    echo "<script>window.location.href='view_animal.php?id=$id&msg=success';</script>";
    exit;
}

// Lấy dữ liệu
$sql = "SELECT * FROM table1 WHERE id = $id";
$res = mysqli_query($link, $sql);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    echo "Không tìm thấy dữ liệu.";
    exit;
}

$edit_mode = isset($_GET['edit']);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Hồ sơ: <?php echo $data['AName']; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    
    <style>
        body {
            background-color: #f4f7f6; /* Màu nền xám nhẹ */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Khung chính chứa thông tin (Panel) */
        .profile-panel {
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); /* Tạo bóng đổ nổi lên */
            border-radius: 8px; /* Bo tròn góc */
            overflow: hidden;
        }
        .profile-panel .panel-heading {
            background-color: #2f7a2f; /* Màu xanh lá chủ đạo */
            color: white;
            font-size: 18px;
            font-weight: bold;
            padding: 15px 20px;
            border: none;
        }
        
        /* Ảnh đại diện */
        .animal-photo {
            width: 100%;
            height: 350px; /* Cố định chiều cao */
            object-fit: cover; /* Cắt ảnh tự động để vừa khung, không méo */
            border-radius: 6px;
            border: 5px solid #fff; /* Viền trắng quanh ảnh */
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Bảng thông tin */
        .info-table th {
            width: 30%;
            color: #7f8c8d; /* Màu chữ nhạt cho tiêu đề */
            font-weight: 600;
        }
        .info-table td {
            font-weight: 500;
            color: #2c3e50; /* Màu chữ đậm cho dữ liệu */
        }
        
        /* Nút bấm */
        .btn-action {
            padding: 8px 20px;
            border-radius: 20px; /* Nút bo tròn */
            font-weight: bold;
            margin-right: 10px;
        }
    </style>
</head>

<body>
<div class="container">

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success') { ?>
        <div id="success-alert" class="alert alert-success alert-dismissible">
            <button type="button" class="close" onclick="this.parentElement.style.display='none';">&times;</button>
            <strong><span class="glyphicon glyphicon-ok"></span> Thành công!</strong> Dữ liệu đã được cập nhật.
        </div>
    <?php } ?>

    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            
            <div class="panel panel-default profile-panel">
                <div class="panel-heading">
                    <span class="glyphicon glyphicon-folder-open"></span> Hồ sơ chi tiết
                </div>
                
                <div class="panel-body" style="padding: 30px;">
                    <div class="row">
                        
                        <div class="col-md-5 text-center">
                            <img src="<?php echo !empty($data['photo']) ? $data['photo'] : 'uploads/default.png'; ?>" class="animal-photo">
                            <h3 class="text-success" style="margin-top: 20px; font-weight: bold;">
                                <?php echo $data['AName']; ?>
                            </h3>
                        </div>

                        <div class="col-md-7">
                            
                            <?php if (!$edit_mode): ?>
                                <table class="table table-hover info-table" style="margin-top: 10px;">
                                    <tbody>
                                        <tr>
                                            <th>ID Hệ thống:</th>
                                            <td><span class="label label-default">#<?php echo $data['id']; ?></span></td>
                                        </tr>
                                        <tr>
                                            <th>Giống loài:</th>
                                            <td><?php echo $data['Species']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Khu vực sống:</th>
                                            <td><span class="text-primary"><?php echo $data['Area']; ?></span></td>
                                        </tr>
                                        <tr>
                                            <th>Ngày sinh:</th>
                                            <td><?php echo date("d-m-Y", strtotime($data['Date'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Mô tả / Tình trạng:</th>
                                            <td><?php echo $data['des']; ?></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <hr> <div class="text-right">
                                    <a href="edit.php?id=<?php echo $id; ?>&edit=1" class="btn btn-warning btn-action">
                                        <span class="glyphicon glyphicon-pencil"></span> Chỉnh sửa
                                    </a>
                                    <a href="homescreen.php" class="btn btn-default btn-action">
                                        <span class="glyphicon glyphicon-arrow-left"></span> Quay lại
                                    </a>
                                </div>

                            <?php else: ?>
                                <form method="post" class="form-horizontal">
                                    <div class="form-group">
                                        <label>Tên động vật</label>
                                        <input type="text" name="AName" class="form-control" value="<?php echo $data['AName']; ?>">
                                    </div>
                                    <div class="text-right" style="margin-top: 20px;">
                                        <button type="submit" name="update" class="btn btn-success btn-action">Lưu lại</button>
                                        <a href="view_animal.php?id=<?php echo $id; ?>" class="btn btn-default btn-action">Hủy bỏ</a>
                                    </div>
                                </form>
                            <?php endif; ?>

                        </div> </div> </div> </div> </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        if ($("#success-alert").length) {
            // Xóa đuôi URL
            if (window.history.replaceState) {
                var cleanUrl = window.location.href.split('&msg=')[0];
                window.history.replaceState({path: cleanUrl}, '', cleanUrl);
            }
            // Ẩn sau 3s
            setTimeout(function() { $("#success-alert").fadeOut(); }, 3000);
        }
    });
</script>

</body>
</html>

