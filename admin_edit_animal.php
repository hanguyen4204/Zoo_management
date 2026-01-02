<?php
session_start();
include "connection.php";

// Check quyền
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit(); }

// 1. KIỂM TRA ID TRÊN URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_animals.php");
    exit();
}

$id = intval($_GET['id']);

// 2. LẤY DỮ LIỆU CŨ TỪ DATABASE
$query = mysqli_query($link, "SELECT * FROM table1 WHERE id = $id");
$row = mysqli_fetch_assoc($query);

if (!$row) {
    echo "Không tìm thấy động vật này!";
    exit();
}

// 3. XỬ LÝ KHI BẤM NÚT "CẬP NHẬT"
if (isset($_POST['update'])) {
    $aname = mysqli_real_escape_string($link, $_POST['aname']);
    $species = mysqli_real_escape_string($link, $_POST['species']);
    $desc = mysqli_real_escape_string($link, $_POST['des']);
    $area = mysqli_real_escape_string($link, $_POST['area']);
    $zone_id = intval($_POST['zone_id']);

    // Logic xử lý ảnh:
    // Mặc định lấy ảnh cũ
    $imagePath = $row['photo']; 

    // Nếu người dùng có chọn ảnh mới
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/";
        $fileName = time() . "_" . basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $fileName;
        
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $imagePath = $target_file; // Cập nhật đường dẫn mới
        }
    }

    // Câu lệnh UPDATE
    $sql = "UPDATE table1 SET 
            AName = '$aname', 
            Species = '$species', 
            Area = '$area', 
            photo = '$imagePath', 
            des = '$desc', 
            zone_id = '$zone_id' 
            WHERE id = $id";
    
    if (mysqli_query($link, $sql)) {
        header("Location: admin_animals.php?msg=updated");
    } else {
        $error = "Lỗi: " . mysqli_error($link);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Animal | Zoo Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; padding-bottom: 50px; }
        .main-container { max-width: 800px; margin: 40px auto; }
        .admin-card { background: #fff; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; border: none; }
        
        /* Header màu Cam để phân biệt với trang Add (Xanh) */
        .card-header-custom { background: #d35400; color: #fff; padding: 25px; text-align: center; }
        .card-header-custom h3 { margin: 0; font-weight: 800; font-size: 24px; }
        
        .card-body-custom { padding: 30px; }
        .form-group label { font-weight: 700; color: #555; font-size: 14px; text-transform: uppercase; }
        .form-control { border-radius: 8px; height: 45px; border: 1px solid #e0e0e0; background: #fdfdfd; }
        .form-control:focus { border-color: #d35400; box-shadow: 0 0 0 3px rgba(211, 84, 0, 0.1); background: #fff; }
        textarea.form-control { height: auto; }

        .img-preview-box { width: 100%; height: 250px; background: #f4f6f9; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-top: 10px; position: relative; }
        .img-preview-box img { width: 100%; height: 100%; object-fit: cover; }
        
        .btn-submit { background: #d35400; color: #fff; font-weight: 800; padding: 12px 30px; border-radius: 50px; border: none; transition: 0.3s; width: 100%; }
        .btn-submit:hover { background: #bf4a00; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .btn-back { color: #666; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="main-container">
    
    <a href="admin_animals.php" class="btn-back"><i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách</a>

    <div class="admin-card">
        <div class="card-header-custom">
            <h3><i class="fas fa-edit"></i> Chỉnh Sửa Thông Tin</h3>
        </div>
        
        <div class="card-body-custom">
            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <form action="" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tên động vật</label>
                            <input type="text" name="aname" class="form-control" required value="<?= $row['AName'] ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Loài (Species)</label>
                            <input type="text" name="species" class="form-control" value="<?= $row['Species'] ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Vị trí / Chuồng</label>
                            <input type="text" name="area" class="form-control" value="<?= $row['Area'] ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Thuộc Khu Vực (Zone)</label>
                            <select name="zone_id" class="form-control" required>
                                <option value="">-- Chọn Zone --</option>
                                <?php
                                $zones = mysqli_query($link, "SELECT * FROM zones");
                                while($z = mysqli_fetch_array($zones)){
                                    // Kiểm tra xem ID nào khớp với dữ liệu cũ thì thêm thuộc tính 'selected'
                                    $selected = ($z['id'] == $row['zone_id']) ? "selected" : "";
                                    echo "<option value='".$z['id']."' $selected>".$z['zone_name']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mô tả chi tiết</label>
                    <textarea name="des" class="form-control" rows="5"><?= $row['des'] ?></textarea>
                </div>

                <div class="form-group">
                    <label>Ảnh đại diện (Chọn nếu muốn thay đổi)</label>
                    <div class="custom-file">
                        <input type="file" name="photo" class="custom-file-input" id="customFile" accept="image/*" onchange="previewImage(this)">
                        <label class="custom-file-label" for="customFile">Giữ ảnh cũ hoặc chọn ảnh mới...</label>
                    </div>
                    <div class="img-preview-box">
                        <img id="preview" src="<?= !empty($row['photo']) ? $row['photo'] : 'uploads/animal_default.png' ?>" style="display: block;">
                    </div>
                </div>

                <hr class="mt-4 mb-4">
                <button type="submit" name="update" class="btn-submit">CẬP NHẬT THAY ĐỔI</button>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        var preview = document.getElementById('preview');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
            var fileName = input.files[0].name;
            document.querySelector('.custom-file-label').innerHTML = fileName;
        }
    }
</script>

</body>
</html>