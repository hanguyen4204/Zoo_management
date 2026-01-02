<?php
include "connection.php";

/* ================= GET DATA ================= */
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
if ($id <= 0) {
    die("ID không hợp lệ");
}

$AName = $Species = $Area = $Date = $des = "";

$res = mysqli_query($link, "SELECT * FROM table1 WHERE id=$id");
if ($row = mysqli_fetch_assoc($res)) {
    $AName   = $row["AName"];
    $Species = $row["Species"];
    $Area    = $row["Area"];
    $Date    = $row["Date"];
    $des     = $row["des"];
}

/* ================= UPDATE DATA ================= */
if (isset($_POST["update"])) {

    $AName   = $_POST["AName"];
    $Species = $_POST["Species"];
    $Area    = $_POST["Area"];
    $Date    = $_POST["Date"];
    $des     = $_POST["des"];

    $sql = "UPDATE table1 SET 
            AName='$AName',
            Species='$Species',
            Area='$Area',
            Date='$Date',
            des='$des'";

    /* ===== Upload image ===== */
    if (!empty($_FILES["photo"]["name"])) {

        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_name = time() . "_" . basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $file_name;

        $check = getimagesize($_FILES["photo"]["tmp_name"]);
        if ($check !== false) {
            move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);
            $sql .= ", photo='$target_file'";
        }
    }

    $sql .= " WHERE id=$id";

    if (mysqli_query($link, $sql)) {
        header("Location: view_animal.php?id=$id&msg=success");
        exit;
    } else {
        echo "Lỗi SQL: " . mysqli_error($link);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa thông tin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 3 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #d4fc79, #96e6a1);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .edit-card {
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.18);
            margin-top: 60px;
            animation: fadeIn 0.6s ease;
        }

        h2 {
            text-align: center;
            color: #2e7d32;
            font-weight: 700;
            margin-bottom: 25px;
        }

        label {
            font-weight: 600;
            color: #444;
        }

        .form-control {
            border-radius: 8px;
            height: 42px;
            border: 1px solid #ccc;
        }

        .form-control:focus {
            border-color: #4caf50;
            box-shadow: 0 0 6px rgba(76,175,80,0.4);
        }

        textarea.form-control {
            height: 90px;
            resize: none;
        }

        input[type="file"] {
            padding: 6px;
        }

        .btn-update {
            width: 100%;
            background: #4caf50;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 10px;
        }

        .btn-update:hover {
            background: #388e3c;
            color: #fff;
        }

        .btn-back {
            width: 100%;
            margin-top: 10px;
            border-radius: 8px;
            font-weight: 600;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

<div class="container">
    <div class="row">
        <div class="col-lg-4 col-lg-offset-4 edit-card">
            <h2>🐾 Chỉnh sửa thông tin</h2>

            <form method="post" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Tên</label>
                    <input type="text" class="form-control" name="AName" value="<?= $AName ?>" required>
                </div>

                <div class="form-group">
                    <label>Giống loài</label>
                    <input type="text" class="form-control" name="Species" value="<?= $Species ?>" required>
                </div>

                <div class="form-group">
                    <label>Khu vực</label>
                    <input type="text" class="form-control" name="Area" value="<?= $Area ?>" required>
                </div>

                <div class="form-group">
                    <label>Ngày sinh</label>
                    <input type="date" class="form-control" name="Date" value="<?= $Date ?>" required>
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea class="form-control" name="des" required><?= $des ?></textarea>
                </div>

                <div class="form-group">
                    <label>Ảnh động vật</label>
                    <input type="file" class="form-control" name="photo">
                </div>

                <button type="submit" name="update" class="btn btn-update">
                    <span class="glyphicon glyphicon-save"></span> Cập nhật
                </button>

                <a href="view_animal.php?id=<?= $id ?>" class="btn btn-primary btn-back">
                    <span class="glyphicon glyphicon-arrow-left"></span> Quay lại
                </a>

            </form>
        </div>
    </div>
</div>

</body>
</html>
