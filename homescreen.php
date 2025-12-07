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
<body>
<div class="container">
    
    <div class="row" style="border-bottom: 1px solid #eee; margin-bottom: 20px; padding-bottom: 10px;">
        <div class="col-md-6 col-xs-6">
            <h2 style="margin-top: 10px;">Thông tin động vật</h2>
        </div>
        
        <div class="col-md-6 col-xs-6 text-right">
            <a href="index.php" class="btn btn-danger" style="margin-top: 15px;">Đăng xuất</a>
        </div>
    </div>

    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-12">
            <form action="" method="post" class="form-inline">
                
                <div class="form-group">
                    <label class="sr-only" for="search">Tìm kiếm</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="search_name" placeholder="Nhập tên..." value="<?php if(isset($_POST['search_name'])) echo $_POST['search_name']; ?>" style="width: 300px;">
                        <div class="input-group-btn">
                            <button type="submit" name="search" class="btn btn-primary">
                                Tìm kiếm
                            </button>
                        </div>
                    </div>
                </div>

                <a href="add.php" class="btn btn-success" style="margin-left: 10px;">
                    + Thêm động vật
                </a>

            </form>
        </div>
    </div>

    <?php
    if (isset($_GET['msg']) && $_GET['msg'] == 'success') {
        echo '
        <div id="success-alert" class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <strong>Thành công!</strong> Đã thêm động vật mới.
        </div>
        ';
    }
    ?>
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
</html>