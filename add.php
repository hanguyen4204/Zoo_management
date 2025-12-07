<?php
include "connection.php";
?>

<html lang="en" xmlns="">
    <head>
        <title>Thêm động vật</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE-edge">
        <meta name="viewport" content="width=device-width, initial-scale=0">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="col-lg-4">
            <h2>Thêm động vật</h2>
            <form action="" name="form1" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="firstname">Tên: </label>
                    <input type="text" class="form-control" id="AName" placeholder="" name="AName">
                </div>
                <div class="form-group">
                    <label for="lastname">Giống loài: </label>
                    <input type="text" class="form-control" id="Species" placeholder="" name="Species">
                </div>
                <div class="form-group">
                    <label for="email">Khu vực: </label>
                    <input type="text" class="form-control" id="Area" placeholder="" name="Area">
                </div>
                <div class="form-group">
                    <label for="contact">Ngày sinh</label>
                    <input type="date" class="form-control" id="Date" placeholder="" name="Date">
                </div>
                <div class="form-group">
                    <label for="email">Mô tả: </label>
                    <input type="text" class="form-control" id="Des" placeholder="" name="Des">
                </div>
                <div class="form-group">
                <label for="photo">Tải ảnh lên</label>
                <input type="file" class="form-control" id="photo" name="photo">
            </div>
                <button type="submit" name="insert" class="btn btn-default">Thêm động vật</button>
                <a href="homescreen.php" class="btn btn-primary mt-3">Quay lại</a>
            </form>
            </div>
        </div>
</body>
<?php
if(isset($_POST["insert"]))
{
    $target_file = "";
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        
        $temp_file = "uploads/" . basename($_FILES["photo"]["name"]);
        
        // Chỉ chạy getimagesize khi chắc chắn có file
        $check = getimagesize($_FILES["photo"]["tmp_name"]);

        if ($check !== false) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $temp_file)) {
                // Upload thành công -> Cập nhật lại đường dẫn ảnh
                $target_file = $temp_file;
            } else {
                echo "Lỗi khi di chuyển file.";
            }
        } else {
            echo "File không phải là ảnh hợp lệ.";
        }
    }
    $sql = "INSERT INTO table1 (AName, Species, Area, Date, photo, des) 
            VALUES ('$_POST[AName]', '$_POST[Species]', '$_POST[Area]', '$_POST[Date]', '$target_file', '$_POST[Des]')";

    if (mysqli_query($link, $sql)) {
         echo "<script>window.location.href = 'homescreen.php?msg=success';</script>";
    } else {
         echo "Lỗi SQL: " . mysqli_error($link);
    }
}
?>
</html>