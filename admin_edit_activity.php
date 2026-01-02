<?php
session_start();
include "connection.php";
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit(); }

$id = intval($_GET['id']);
$query = mysqli_query($link, "SELECT * FROM activity WHERE id = $id");
$row = mysqli_fetch_assoc($query);

if (isset($_POST['update'])) {
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $desc = mysqli_real_escape_string($link, $_POST['description']);
    $time = $_POST['time'];
    $file_link = mysqli_real_escape_string($link, $_POST['file_link']);
    $zone_id = intval($_POST['zone_id']);

    $imagePath = $row['image'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/";
        $fileName = time() . "_act_" . basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $fileName;
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $imagePath = $target_file;
        }
    }

    $sql = "UPDATE activity SET 
            name='$name', description='$desc', time='$time', file_link='$file_link', 
            image='$imagePath', zone_id='$zone_id' 
            WHERE id=$id";
    mysqli_query($link, $sql);
    header("Location: admin_activities.php?msg=updated");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Activity | Zoo Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>body { background: #f4f6f9; padding: 40px; } .form-card { max-width: 700px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }</style>
</head>
<body>
    <div class="form-card">
        <h3 class="font-weight-bold text-warning mb-4">Sửa Sự Kiện</h3>
        
        <form action="" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Tên sự kiện</label>
                        <input type="text" name="name" class="form-control" required value="<?= $row['name'] ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Thời gian</label>
                        <input type="time" name="time" class="form-control" required value="<?= $row['time'] ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Diễn ra tại khu vực</label>
                        <select name="zone_id" class="form-control" required>
                            <option value="">-- Chọn khu vực --</option>
                            <?php
                            $zones = mysqli_query($link, "SELECT * FROM zones");
                            while($z = mysqli_fetch_array($zones)){
                                $selected = ($z['id'] == $row['zone_id']) ? "selected" : "";
                                echo "<option value='".$z['id']."' $selected>".$z['zone_name']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Link chi tiết</label>
                        <input type="text" name="file_link" class="form-control" value="<?= $row['file_link'] ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Mô tả ngắn</label>
                <textarea name="description" class="form-control" rows="3"><?= $row['description'] ?></textarea>
            </div>

            <div class="form-group">
                <label>Ảnh bìa (Chọn nếu muốn thay đổi)</label>
                <input type="file" name="photo" class="form-control-file">
                <img src="<?= $row['image'] ?>" style="height: 100px; margin-top: 10px; border-radius: 5px;">
            </div>

            <button type="submit" name="update" class="btn btn-warning text-white btn-block font-weight-bold mt-4">Cập nhật</button>
            <a href="admin_activities.php" class="btn btn-link btn-block">Hủy</a>
        </form>
    </div>
</body>
</html>