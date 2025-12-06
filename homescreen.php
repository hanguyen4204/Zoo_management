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
    <!-- short column display for forms rows -->
   <!--visit https://www.w3schools.com/bootstrap/bootstrap_forms.asp search for forms template and use it.-->
    <div class="col-lg-4">
        <h2>Thông tin động vật</h2>
        <form action="" name="form1" method="post" enctype="multipart/form-data">
        <div class="form-group">
        <label for="search">Tìm kiếm bằng tên</label>
        <input type="text" class="form-control" id="search" name="search_name" placeholder="Enter firstname to search">
    </div>
        <a href="add.php" class="btn btn-default" type="submit" name="insert" >Thêm động vật</a>
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

</html>