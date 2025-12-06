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
                <input type="file" class="form-control" required id="photo" name="photo">
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
    // mysqli_query($link,"insert into table1 values (NULL,'$_POST[firstname]' ,'$_POST[lastname]','$_POST[email]','$_POST[contact]','$_FILES[photo]')");
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["photo"]["name"]);
    // move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if ($check !== false) {
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);

        // Lưu đường dẫn ảnh vào DB
        mysqli_query($link, "INSERT INTO table1 VALUES (NULL,'$_POST[AName]' ,'$_POST[Species]','$_POST[Area]','$_POST[Date]','$target_file','$_POST[Des]')");

        echo "<script>window.location.href = window.location.href;</script>";
    } else {
        echo "<div class='alert alert-danger'>File không phải là ảnh hợp lệ.</div>";
    }
}

?>
</html>