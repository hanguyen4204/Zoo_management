<?php
include "connection.php";
?>

<html lang="en" xmlns="">
<head>
    <title>Thong tin dong vat</title>
    <meta charset="utf-8">
     <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
    <body style="background-color: #f4f6f9;"> <nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">🦁 Zoo Admin</a>
            <div class="ml-auto">
                <a href="add_animal.php" class="btn btn-light btn-sm">+ Thêm mới</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm ml-2">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Tổng số con vật</h5>
                        <p class="card-text display-4">25</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Loài vật</h5>
                        <p class="card-text display-4">8</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Cần chú ý</h5>
                        <p class="card-text display-4">2</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-white border-0">
                <h4 class="mb-0">Danh sách Động vật</h4>
            </div>
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Ảnh</th>
                            <th>Tên</th>
                            <th>Mô tả</th>
                            <th class="text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>19</td>
                            <td><img src="uploads/ho.jpg" class="rounded-circle" width="50" height="50" style="object-fit: cover;"></td>
                            <td><strong>Mimi</strong></td>
                            <td>Con hổ trắng béo nhất...</td>
                            <td class="text-right">
                                <a href="#" class="btn btn-sm btn-info text-white">Xem</a>
                                <a href="#" class="btn btn-sm btn-danger">Xóa</a>
                            </td>
                        </tr>
                         </tbody>
                </table>
            </div>
        </div>
    </div>

        <button type="submit" name="insert" class="btn btn-default">Thêm động vật</button>
        <button type="submit" name="search" class="btn btn-primary" formnovalidate>Tìm kiếm</button>
        <a href="index.php" class="btn btn-primary mt-3">Đăng xuất</a>
    </form>
</div>
</div>

<!-- new column inserted for records -->
<!-- Search for boostrap table template online and copy code -->
<div class="col-lg-12">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Id</th>
            <th>Ảnh</th>
            <th>Tên</th>
            <th>Mô tả </th>
            <th>Xóa</th>
            <th>Thông tin chi tiết</th>
        </tr>
        </thead>
        <tbody>
        <!-- Database connection -->
        <?php
        if (!empty($link)) {
    if (isset($_POST["search"]) && !empty($_POST["search_name"])) {
        $search_name = mysqli_real_escape_string($link, $_POST["search_name"]);
        setcookie("last_search", $search_name, time() + (86400 * 30), "/");
        $res = mysqli_query($link, "SELECT * FROM table1 WHERE AName LIKE '%$search_name%'");
    } else {
        $res = mysqli_query($link, "SELECT * FROM table1");
    }
}

        while($row=mysqli_fetch_array($res))
        {
            echo "<tr>";
            echo "<td>"; echo $row["id"]; echo "</td>";
            echo "<td><img src='" . $row["photo"] . "' width='80'></td>";
            echo "<td>"; echo $row["AName"]; echo "</td>";
            echo "<td>"; echo $row["des"]; echo "</td>";
            echo "<td><a href='delete.php?id=" . (int)$row['id'] . "' onclick=\"return confirm('Xác nhận xóa?')\"><button type='button' class='btn btn-danger'>Xóa</button></a></td>";
            echo "<td>"; ?> <a href="view_animal.php?id=<?php echo $row["id"]; ?>"><button type="button" class="btn btn-info">Chi tiết </button></a> <?php echo "</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>

<!-- new records insertion into database table -->
<!-- records delete from database table -->
<!-- records update from database table -->

<!-- to automatically refresh the pages after crud activity   window.location.href=window.location.href; -->
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