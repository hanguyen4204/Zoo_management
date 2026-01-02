<?php
session_start();
include "connection.php";

// 1. CHECK QUYỀN
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit(); }
$current_id = $_SESSION['id_user'];
$check_admin = mysqli_query($link, "SELECT role FROM users WHERE id_user = '$current_id'");
$user = mysqli_fetch_array($check_admin);
if ($user['role'] != 'admin') { header("Location: homescreen.php"); exit(); }

// 2. XỬ LÝ XÓA
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($link, "DELETE FROM activity WHERE id = $id");
    header("Location: admin_activities.php?msg=deleted");
    exit();
}

// 3. XỬ LÝ TÌM KIẾM
$search_term = "";
$where_clause = "";
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_term = mysqli_real_escape_string($link, $_GET['q']);
    $where_clause = " WHERE a.name LIKE '%$search_term%' ";
}

// 4. LẤY DANH SÁCH (JOIN với bảng ZONES để biết sự kiện ở khu nào)
$sql = "SELECT a.*, z.zone_name 
        FROM activity a 
        LEFT JOIN zones z ON a.zone_id = z.id 
        $where_clause
        ORDER BY a.time ASC"; // Sắp xếp theo giờ tăng dần
$res = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Activities | Zoo Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        /* Sidebar Styles (Đồng bộ) */
        .sidebar { height: 100vh; width: 250px; position: fixed; top: 0; left: 0; background: #0b3d2e; color: #fff; z-index: 1000; }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { color: #f4f91d; margin:0; font-weight:800; }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a { padding: 15px 25px; display: block; color: #ccc; text-decoration: none; font-weight: 600; border-left: 4px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.05); color: #fff; border-left-color: #f4f91d; }
        .sidebar-menu i { width: 25px; }
        .main-content { margin-left: 250px; padding: 20px; }
        
        .table-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .thumb-img { width: 80px; height: 50px; object-fit: cover; border-radius: 5px; }
        .time-badge { background: #f4f91d; color: #0b3d2e; padding: 5px 10px; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header"><h3>🦁 ZOO ADMIN</h3></div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_animals.php"><i class="fas fa-paw"></i> Quản lý Thú</a>
            <a href="admin_zones.php"><i class="fas fa-map-marked-alt"></i> Khu vực (Zones)</a>
            <a href="admin_activities.php" class="active"><i class="fas fa-calendar-alt"></i> Sự kiện</a>
            <a href="admin_tickets.php"><i class="fas fa-ticket-alt"></i> Vé đặt</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Người dùng</a>
            <a href="admin_social.php"><i class="fas fa-comments"></i> Mạng xã hội</a>
            <a href="homescreen.php" target="_blank" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1)"><i class="fas fa-external-link-alt"></i> Xem trang chủ</a>
            <a href="logout.php" class="text-danger mt-3"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-dark font-weight-bold">Quản Lý Sự Kiện / Show</h2>
            <a href="admin_add_activity.php" class="btn btn-success rounded-pill font-weight-bold shadow-sm">
                <i class="fas fa-plus-circle"></i> Thêm Sự Kiện
            </a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
            <div class="alert alert-success">Đã xóa sự kiện thành công!</div>
        <?php endif; ?>

        <div class="table-card">
            <div class="row mb-3">
                <div class="col-md-5">
                    <form action="" method="GET" class="d-flex">
                        <input type="text" name="q" class="form-control" placeholder="Tìm tên sự kiện..." value="<?= htmlspecialchars($search_term) ?>">
                        <button class="btn btn-primary ml-1"><i class="fas fa-search"></i></button>
                        <?php if(!empty($search_term)): ?>
                            <a href="admin_activities.php" class="btn btn-secondary ml-1"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <table class="table table-hover align-middle">
                <thead class="thead-light">
                    <tr>
                        <th>Thời gian</th>
                        <th>Hình ảnh</th>
                        <th>Tên sự kiện</th>
                        <th>Địa điểm (Zone)</th>
                        <th>Link / File</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_array($res)): 
                        $img = !empty($row['image']) ? $row['image'] : 'uploads/default.jpg';
                        $timeDisplay = date('H:i', strtotime($row['time']));
                        $zoneName = !empty($row['zone_name']) ? $row['zone_name'] : "Chưa xác định";
                    ?>
                    <tr>
                        <td><span class="time-badge"><?= $timeDisplay ?></span></td>
                        <td><img src="<?= $img ?>" class="thumb-img"></td>
                        <td><strong><?= $row['name'] ?></strong></td>
                        <td><span class="badge badge-info"><?= $zoneName ?></span></td>
                        <td>
                            <?php if(!empty($row['file_link']) && $row['file_link'] != '#'): ?>
                                <a href="<?= $row['file_link'] ?>" target="_blank" class="text-primary"><i class="fas fa-link"></i> Link</a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="admin_edit_activity.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm rounded-circle"><i class="fas fa-pen"></i></a>
                            <a href="admin_activities.php?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm rounded-circle" onclick="return confirm('Xóa sự kiện này?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>