<?php
include "connection.php";
?>
<html lang="en" xmlns="">
<head>
    <title>Thông tin chi tiết</title>
    <meta charset="utf-8">
     <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    
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
        }
    </style>
</head>

<body>

<div class="container mt-5">
    <?php
        if (isset($_GET['msg']) && $_GET['msg'] == 'success') {
            echo '
            <div id="success-alert" class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Thành công!</strong> Thông tin động vật đã được cập nhật.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            ';
            }
    ?>

    <h2 class="mb-4">Thông tin chi tiết động vật</h2>

    <div class="row">

        <!-- Ảnh -->
        <div class="col-md-4">
            <img src="<?php echo $data['photo']; ?>" class="profile-img">
        </div>

        <div class="col-md-8 detail-box">

            <?php if (!$edit_mode): ?>
                <!-- Chế độ xem -->
                <p><span class="detail-label">ID:</span> <?php echo $data['id']; ?></p>
                <p><span class="detail-label">Tên:</span> <?php echo $data['AName']; ?></p>
                <p><span class="detail-label">Giống loài:</span> <?php echo $data['Species']; ?></p>
                <p><span class="detail-label">Khu vực:</span> <?php echo $data['Area']; ?></p>
                <p><span class="detail-label">Ngày sinh:</span> <?php echo $data['Date']; ?></p>
                <p><span class="detail-label">Mô tả:</span> <?php echo $data['des']; ?></p>

                <a href="edit.php?id=<?php echo $id; ?>&edit=1" class="btn btn-warning mt-3">Chỉnh sửa</a>
                <a href="homescreen.php" class="btn btn-primary mt-3">Quay lại</a>

            <?php else: ?>

                <!-- Chế độ Edit -->
                <form method="post">

                    <div class="form-group">
                        <label>Tên</label>
                        <input type="text" name="AName" class="form-control"
                               value="<?php echo $data['AName']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Giống loài</label>
                        <input type="text" name="Species" class="form-control"
                               value="<?php echo $data['Species']; ?>">         
                    </div>
                    <div class="form-group">
                        <label>Khu vực</label>
                        <input type="text" name="Area" class="form-control"
                               value="<?php echo $data['Area']; ?>">   
                    </div>
                    <div class="form-group">
                        <label>Ngày sinh</label>
                        <input type="date" name="Date" class="form-control"
                               value="<?php echo $data['Date']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Mô tả</label>
                        <input type="text" name="des" class="form-control"
                               value="<?php echo $data['des']; ?>">
                    </div>
                    

                    <button type="submit" name="update" class="btn btn-success">Lưu</button>
                    <a href="view_animal.php?id=<?php echo $id; ?>" class="btn btn-secondary">Thoát</a>

                </form>

            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>

<?php
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

    echo "<script>alert('Cập nhật thành công!'); window.location.href='edit.php?id=$id';</script>";
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


