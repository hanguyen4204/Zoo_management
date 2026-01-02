<?php
session_start();
include "connection.php";

// 1. CHECK QUYỀN ADMIN
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit(); }
$current_id = $_SESSION['id_user'];
$check_admin = mysqli_query($link, "SELECT role FROM users WHERE id_user = '$current_id'");
$user_data = mysqli_fetch_array($check_admin);
if ($user_data['role'] != 'admin') { header("Location: homescreen.php"); exit(); }

// 2. XỬ LÝ XÓA
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($link, "DELETE FROM table1 WHERE id = $id");
    header("Location: admin_animals.php?msg=deleted");
    exit();
}

// 3. XỬ LÝ TÌM KIẾM & LẤY DANH SÁCH
$search_term = "";
$where_clause = "";

// Kiểm tra xem có từ khóa tìm kiếm không
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_term = mysqli_real_escape_string($link, $_GET['q']);
    // Tìm trong cột AName (Tên) HOẶC Species (Loài)
    $where_clause = " WHERE t.AName LIKE '%$search_term%' OR t.Species LIKE '%$search_term%' ";
}

// Câu lệnh SQL động
$sql = "SELECT t.*, z.zone_name 
        FROM table1 t 
        LEFT JOIN zones z ON t.zone_id = z.id 
        $where_clause 
        ORDER BY t.id DESC";

$res = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Animals | Zoo Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        
        /* Sidebar Styles */
        .sidebar { height: 100vh; width: 250px; position: fixed; top: 0; left: 0; background: #0b3d2e; color: #fff; z-index: 1000; }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { color: #f4f91d; margin:0; font-weight:800;}
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a { padding: 15px 25px; display: block; color: #ccc; text-decoration: none; font-weight: 600; border-left: 4px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.05); color: #fff; border-left-color: #f4f91d; }
        .sidebar-menu i { width: 25px; }
        .main-content { margin-left: 250px; padding: 20px; }

        /* Table & Search Styles */
        .table-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .thumb-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        .action-btn { width: 35px; height: 35px; border-radius: 50%; padding: 0; display: inline-flex; align-items: center; justify-content: center; margin: 0 3px; }
        
        /* Search Form Custom */
        .search-form { max-width: 400px; }
        .search-form .form-control { border-radius: 20px 0 0 20px; }
        .search-form .btn { border-radius: 0 20px 20px 0; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header"><h3>🦁 ZOO ADMIN</h3></div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_animals.php" class="active"><i class="fas fa-paw"></i> Quản lý Thú</a>
            <a href="admin_zones.php"><i class="fas fa-map-marked-alt"></i> Khu vực (Zones)</a>
            <a href="admin_activities.php"><i class="fas fa-calendar-alt"></i> Sự kiện</a>
            <a href="admin_tickets.php"><i class="fas fa-ticket-alt"></i> Vé đặt</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Người dùng</a>
            <a href="admin_social.php"><i class="fas fa-comments"></i> Mạng xã hội</a>
            <a href="homescreen.php" target="_blank" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1)"><i class="fas fa-external-link-alt"></i> Xem trang chủ</a>
            <a href="logout.php" class="text-danger mt-3"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-dark font-weight-bold">Danh Sách Động Vật</h2>
            <a href="admin_add_animal.php" class="btn btn-success rounded-pill font-weight-bold shadow-sm">
                <i class="fas fa-plus-circle"></i> Thêm Mới
            </a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
            <div class="alert alert-success">Đã xóa động vật thành công!</div>
        <?php endif; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg']=='added'): ?>
            <div class="alert alert-success">Thêm động vật thành công!</div>
        <?php endif; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg']=='updated'): ?>
            <div class="alert alert-info">Cập nhật thông tin thành công!</div>
        <?php endif; ?>

        <div class="table-card">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <form action="" method="GET" class="search-form d-flex">
                        <input type="text" name="q" class="form-control" placeholder="Tìm theo tên hoặc loài..." value="<?= htmlspecialchars($search_term) ?>">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                        
                        <?php if(!empty($search_term)): ?>
                            <a href="admin_animals.php" class="btn btn-secondary ml-2" style="border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;" title="Hủy tìm kiếm">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="col-md-6 text-right text-muted pt-2">
                    <?php if(!empty($search_term)): ?>
                        Kết quả cho: "<strong><?= htmlspecialchars($search_term) ?></strong>"
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th>#ID</th>
                            <th>Hình ảnh</th>
                            <th>Tên gọi</th>
                            <th>Loài (Species)</th>
                            <th>Khu vực (Zone)</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($res) > 0) {
                            while($row = mysqli_fetch_array($res)): 
                                $img = !empty($row['photo']) ? $row['photo'] : 'uploads/animal_default.png';
                                $zoneDisplay = !empty($row['zone_name']) ? '<span class="badge badge-info">'.$row['zone_name'].'</span>' : '<span class="badge badge-secondary">Chưa phân loại</span>';
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><img src="<?= $img ?>" class="thumb-img"></td>
                            <td><strong><?= $row['AName'] ?></strong></td>
                            <td><?= $row['Species'] ?></td>
                            <td><?= $zoneDisplay ?></td>
                            <td class="text-center">
                                <a href="admin_edit_animal.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm action-btn" title="Sửa">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="admin_animals.php?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm action-btn" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa con vật này không?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-4'>Không tìm thấy dữ liệu phù hợp.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>