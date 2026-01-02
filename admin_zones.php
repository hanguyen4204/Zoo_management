<?php
session_start();
include "connection.php";

// 1. CHECK QUYỀN ADMIN
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit(); }
$current_id = $_SESSION['id_user'];
$check_admin = mysqli_query($link, "SELECT role FROM users WHERE id_user = '$current_id'");
$user_data = mysqli_fetch_array($check_admin);
if ($user_data['role'] != 'admin') { header("Location: homescreen.php"); exit(); }

// 2. XỬ LÝ LOGIC HIỂN THỊ
$view_zone_id = isset($_GET['view_id']) ? intval($_GET['view_id']) : null;

// Nếu đang xem chi tiết (Có view_id)
if ($view_zone_id) {
    // Lấy tên Zone
    $zone_query = mysqli_query($link, "SELECT zone_name FROM zones WHERE id = $view_zone_id");
    $zone_info = mysqli_fetch_assoc($zone_query);
    
    // Lấy danh sách thú trong Zone đó
    $animals_query = mysqli_query($link, "SELECT * FROM table1 WHERE zone_id = $view_zone_id ORDER BY id DESC");
} else {
    // Nếu ở màn hình chính (Lấy danh sách Zone + Đếm số thú)
    $zones_query = mysqli_query($link, "SELECT z.*, 
                    (SELECT COUNT(*) FROM table1 WHERE zone_id = z.id) as total 
                    FROM zones z");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zone Management | Zoo Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        
        /* Sidebar (Giữ nguyên từ Dashboard) */
        .sidebar { height: 100vh; width: 250px; position: fixed; top: 0; left: 0; background: #0b3d2e; color: #fff; z-index: 1000; }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { color: #f4f91d; margin:0; font-weight:800; font-size: 22px; }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a { padding: 15px 25px; display: block; color: #ccc; text-decoration: none; font-weight: 600; border-left: 4px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.05); color: #fff; border-left-color: #f4f91d; }
        .sidebar-menu i { width: 25px; }
        .main-content { margin-left: 250px; padding: 30px; }

        /* Card Zone Style */
        .zone-card {
            background: #fff; border-radius: 12px; border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: 0.3s; cursor: pointer; text-decoration: none !important;
            display: block; color: #333; height: 100%;
        }
        .zone-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); color: #0b3d2e; }
        .zone-card .card-body { padding: 25px; text-align: center; }
        .zone-icon { font-size: 50px; margin-bottom: 15px; color: #0b3d2e; }
        .zone-count { background: #f4f91d; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: 700; color: #0b3d2e; }

        /* Animal List Style */
        .animal-item {
            background: #fff; padding: 15px; border-bottom: 1px solid #eee;
            display: flex; align-items: center;
        }
        .animal-item:last-child { border-bottom: none; }
        .animal-thumb { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; margin-right: 15px; border: 1px solid #ddd; }
        
        .btn-back { background: #e9ecef; color: #333; font-weight: 600; border-radius: 20px; padding: 8px 20px; }
        .btn-back:hover { text-decoration: none; background: #dde2e6; color: #000; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header"><h3>🦁 ZOO ADMIN</h3></div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_animals.php"><i class="fas fa-paw"></i> Quản lý Thú</a>
            <a href="admin_zones.php" class="active"><i class="fas fa-map-marked-alt"></i> Khu vực (Zones)</a>
            <a href="admin_activities.php"><i class="fas fa-calendar-alt"></i> Sự kiện</a>
            <a href="admin_tickets.php"><i class="fas fa-ticket-alt"></i> Vé đặt</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Người dùng</a>
            <a href="admin_social.php"><i class="fas fa-comments"></i> Mạng xã hội</a>
            <a href="homescreen.php" target="_blank" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1)"><i class="fas fa-external-link-alt"></i> Xem trang chủ</a>
            <a href="logout.php" class="text-danger mt-3"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>

    <div class="main-content">

        <?php if ($view_zone_id): ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="admin_zones.php" class="btn-back mb-2"><i class="fas fa-arrow-left"></i> Quay lại</a>
                    <h2 class="font-weight-bold text-dark">
                        Khu vực: <span style="color:#0b3d2e;"><?= $zone_info['zone_name'] ?></span>
                    </h2>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <?php if(mysqli_num_rows($animals_query) > 0): ?>
                        <?php while($ani = mysqli_fetch_array($animals_query)): 
                            $img = !empty($ani['photo']) ? $ani['photo'] : 'uploads/animal_default.png';
                        ?>
                        <div class="animal-item">
                            <img src="<?= $img ?>" class="animal-thumb">
                            <div class="flex-grow-1">
                                <h5 class="mb-1 font-weight-bold"><?= $ani['AName'] ?></h5>
                                <span class="text-muted small"><i class="fas fa-dna"></i> <?= $ani['Species'] ?></span>
                                <span class="text-muted small ml-3"><i class="fas fa-map-pin"></i> <?= $ani['Area'] ?></span>
                            </div>
                            <a href="admin_edit_animal.php?id=<?= $ani['id'] ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Sửa chi tiết">
                                <i class="fas fa-pen"></i>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có con vật nào trong khu vực này.</p>
                            <a href="admin_add_animal.php" class="btn btn-success btn-sm">Thêm thú vào đây</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>

            <h2 class="font-weight-bold text-dark mb-4">Danh Sách Khu Vực</h2>
            
            <div class="row">
                <?php while($z = mysqli_fetch_array($zones_query)): ?>
                <div class="col-md-4 col-sm-6 mb-4">
                    <a href="admin_zones.php?view_id=<?= $z['id'] ?>" class="zone-card">
                        <div class="card-body">
                            <div class="zone-icon"><i class="fas fa-map"></i></div>
                            <h4 class="font-weight-bold"><?= $z['zone_name'] ?></h4>
                            <p class="text-muted small mb-3"><?= substr($z['description'], 0, 60) ?>...</p>
                            <span class="zone-count"><?= $z['total'] ?> Animals</span>
                        </div>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>