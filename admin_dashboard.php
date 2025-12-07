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

    <style>
        /* --- 1. KHAI BÁO MÀU DÙNG CHUNG (Giống hệt trang kia) --- */
        :root {
            --primary-green: #2f7a2f; /* Xanh lá đậm */
            --bg-color: #f4f6f4;      /* Nền xám nhạt */
            --text-color: #333;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
        }

        /* --- 2. THANH HEADER TRÊN CÙNG --- */
        .top-nav {
            background: #fff;
            padding: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border-bottom: 3px solid var(--primary-green);
        }
        .page-title {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-green);
            line-height: 1.5;
        }

        /* --- 3. KHUNG CHỨA BẢNG (MAIN PANEL) --- */
        .main-panel {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 20px;
            border: none; /* Bỏ viền mặc định của BS3 */
        }

        /* --- 4. TÙY CHỈNH BẢNG (TABLE) --- */
        .zoo-table thead tr {
            background-color: var(--primary-green);
            color: white;
        }
        .zoo-table th {
            border-bottom: none !important;
            padding: 15px !important;
            font-weight: 600;
        }
        .zoo-table td {
            padding: 12px 15px !important;
            vertical-align: middle !important; /* Căn giữa chiều dọc */
            border-bottom: 1px solid #f0f0f0;
        }
        .zoo-table tbody tr:hover {
            background-color: #f9fff9; /* Màu nền khi di chuột */
        }
        
        /* Ảnh thumbnail trong bảng */
        .thumb-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border: 2px solid #eee;
        }

        /* --- 5. NÚT BẤM (Bo tròn viên thuốc) --- */
        .btn-pill {
            border-radius: 50px;
            padding: 6px 20px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }
        .btn-pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Màu nút */
        .btn-add { background: var(--primary-green); color: white; }
        .btn-add:hover { background: #256125; color: white; }
        
        .btn-search { background: #f0ad4e; color: white; } /* Màu cam */
        .btn-search:hover { background: #ec971f; color: white; }

        .btn-logout { background: #e74c3c; color: white; }
        .btn-logout:hover { background: #c0392b; color: white; }

        /* Nút hành động nhỏ trong bảng */
        .action-icon {
            margin: 0 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="top-nav">
        <div class="container">
            <div class="row">
                <div class="col-xs-6">
                    <h1 class="page-title">🦁 Quản Lý Sở Thú</h1>
                </div>
                <div class="col-xs-6 text-right">
                    <a href="index.php" class="btn btn-logout btn-pill">
                        <i class="fa fa-sign-out"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </div>

<div class="container">

        <?php
        if (isset($_GET['msg']) && $_GET['msg'] == 'success') {
            echo '
            <div id="success-alert" class="alert alert-success alert-dismissible" style="border-radius: 8px;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><i class="fa fa-check-circle"></i> Thành công!</strong> Dữ liệu đã được cập nhật.
            </div>
            ';
        }
        ?>
<div class="main-panel">
            
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-8">
                    <form action="" method="post" class="form-inline">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search_name" 
                                   placeholder="Nhập tên động vật..." 
                                   value="<?php if(isset($_POST['search_name'])) echo $_POST['search_name']; ?>" 
                                   style="height: 40px; border-radius: 20px 0 0 20px; width: 300px; border: 1px solid #ddd;">
                            <span class="input-group-btn">
                                <button class="btn btn-search" type="submit" name="search" 
                                        style="height: 40px; border-radius: 0 20px 20px 0; padding: 0 20px;">
                                    <i class="fa fa-search"></i> Tìm kiếm
                                </button>
                            </span>
                        </div>
                    </form>
                </div>
                
                <div class="col-md-4 text-right">
                    <a href="add.php" class="btn btn-add btn-pill">
                        <i class="fa fa-plus-circle"></i> Thêm động vật
                    </a>
                </div>
            </div>
<div class="table-responsive">
                <table class="table zoo-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 100px;">Ảnh</th>
                            <th>Tên</th>
                            <th>Mô tả</th>
                            <th class="text-center" style="width: 200px;">Hành động</th>
                        </tr>
                    </thead>
    <tbody>
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

   while($row=mysqli_fetch_array($res)) {
        echo "<tr>";
         // ID
        echo "<td><span class='label label-default'>#" . $row["id"] . "</span></td>";
                            
        // Ảnh (Bo tròn)
        $img_src = !empty($row["photo"]) ? $row["photo"] : 'uploads/default.png';
                            echo "<td><img src='$img_src' class='thumb-img'></td>";
                            
        // Tên (In đậm màu xanh)
           echo "<td><strong style='color: var(--primary-green); font-size: 16px;'>" . $row["AName"] . "</strong></td>";
                            
        // Mô tả
        echo "<td style='color: #666;'>" . $row["des"] . "</td>";
                            
        // Nút hành động (Gom vào 1 cột)
        echo "<td class='text-center'>";
                            
        // Nút Chi tiết (Màu xanh dương nhạt)
        echo "<a href='view_animal.php?id=" . $row["id"] . "' class='btn btn-info btn-xs btn-pill' title='Chi tiết' style='margin-right: 5px;'>";
        echo "<i class='fa fa-eye'></i> Xem";
        echo "</a>";

        // Nút Xóa (Màu đỏ nhạt)
        echo "<a href='delete.php?id=" . $row["id"] . "' onclick=\"return confirm('Bạn chắc chắn muốn xóa " . $row['AName'] . " chứ?')\" class='btn btn-danger btn-xs btn-pill' title='Xóa'>";
        echo "<i class='fa fa-trash'></i> Xóa";
        echo "</a>";
                            
        echo "</td>";
        echo "</tr>";
            }
        ?>
    </tbody>
        </table>
</div>
</body>
</html>
