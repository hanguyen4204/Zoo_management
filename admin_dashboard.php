<?php
session_start();
include "connection.php";

// ================= 1. CHECK QUYỀN ADMIN =================
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$current_id = $_SESSION['id_user'];
$check_admin = mysqli_query($link, "SELECT * FROM users WHERE id_user = '$current_id' AND role = 'admin'");

if (mysqli_num_rows($check_admin) == 0) {
    // Nếu không phải admin, đá về trang chủ
    header("Location: homescreen.php");
    exit();
}

// Lấy thông tin Admin để hiện Avatar
$admin_data = mysqli_fetch_assoc($check_admin);
$admin_name = $admin_data['username'];
$admin_photo = !empty($admin_data['photo']) ? $admin_data['photo'] : "uploads/default_user.png";

// ================= 2. THỐNG KÊ SỐ LIỆU (DASHBOARD) =================
// Đếm số thú
$count_animals = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(*) FROM table1"))[0];

// Đếm số vé đã bán (Giả sử bạn có bảng 'tickets_booked' hoặc tương tự, nếu chưa có tôi để tạm 0)
// $count_tickets = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(*) FROM ticket_history"))[0]; 
$count_tickets = 0; // Tạm thời để 0

// Đếm tổng User
$count_users = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(*) FROM users WHERE role != 'admin'"))[0];

// Đếm sự kiện
$count_activities = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(*) FROM activity"))[0];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Zoo Explorer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        
        /* SIDEBAR STYLE */
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0; left: 0;
            background: #0b3d2e; /* Màu xanh Zoo */
            color: #fff;
            transition: 0.3s;
            z-index: 1000;
        }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { font-weight: 800; margin: 0; color: #f4f91d; font-size: 22px; }
        
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a {
            padding: 15px 25px;
            display: block;
            color: #ccc;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
            border-left: 4px solid transparent;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.05);
            color: #fff;
            border-left-color: #f4f91d;
        }
        .sidebar-menu i { width: 25px; }

        /* MAIN CONTENT STYLE */
        .main-content { margin-left: 250px; padding: 20px; }
        
        /* TOP BAR */
        .topbar {
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-radius: 10px;
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px;
        }
        .admin-profile { display: flex; align-items: center; gap: 10px; }
        .admin-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #f4f91d; }

        /* DASHBOARD CARDS */
        .stat-card {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px;
            transition: 0.3s;
            border-left: 5px solid #0b3d2e;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .stat-icon {
            width: 60px; height: 60px;
            border-radius: 50%;
            background: #e8f5e9;
            color: #0b3d2e;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
        }
        .stat-info h3 { margin: 0; font-size: 28px; font-weight: 800; color: #333; }
        .stat-info p { margin: 0; color: #777; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }

        /* Colors for cards */
        .card-blue { border-left-color: #3498db; } .card-blue .stat-icon { color: #3498db; background: #eaf6fc; }
        .card-orange { border-left-color: #f39c12; } .card-orange .stat-icon { color: #f39c12; background: #fdf5e6; }
        .card-red { border-left-color: #e74c3c; } .card-red .stat-icon { color: #e74c3c; background: #fdedec; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { left: -250px; }
            .sidebar.active { left: 0; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h3>🦁 ZOO ADMIN</h3>
        </div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_animals.php"><i class="fas fa-paw"></i> Quản lý Thú</a>
            <a href="admin_zones.php"><i class="fas fa-map-marked-alt"></i> Khu vực (Zones)</a>
            <a href="admin_activities.php"><i class="fas fa-calendar-alt"></i> Sự kiện</a>
            <a href="admin_tickets.php"><i class="fas fa-ticket-alt"></i> Vé đặt</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Người dùng</a>
            <a href="admin_social.php"><i class="fas fa-comments"></i> Mạng xã hội</a>
            <a href="homescreen.php" target="_blank" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1)"><i class="fas fa-external-link-alt"></i> Xem trang chủ</a>
            <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="topbar">
            <h4 style="margin:0; font-weight:700; color:#0b3d2e;">Dashboard Overview</h4>
            <div class="admin-profile">
                <div class="text-right mr-2">
                    <div style="font-weight:700; font-size:14px;"><?= $admin_name ?></div>
                    <div style="font-size:12px; color:#888;">Administrator</div>
                </div>
                <img src="<?= $admin_photo ?>" class="admin-avatar">
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?= $count_animals ?></h3>
                        <p>Animals</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-paw"></i></div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="stat-card card-blue">
                    <div class="stat-info">
                        <h3><?= $count_users ?></h3>
                        <p>Members</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-user-friends"></i></div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="stat-card card-orange">
                    <div class="stat-info">
                        <h3><?= $count_activities ?></h3>
                        <p>Activities</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="stat-card card-red">
                    <div class="stat-info">
                        <h3><?= $count_tickets ?></h3>
                        <p>Tickets Sold</p>
                    </div>
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-clock text-warning"></i> Recent Animals Added</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead style="background:#f8f9fa;">
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Species</th>
                                    <th>Zone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Lấy 5 con vật mới nhất
                                $sql_recent = "SELECT * FROM table1 ORDER BY id DESC LIMIT 5";
                                $res_recent = mysqli_query($link, $sql_recent);
                                while($row = mysqli_fetch_array($res_recent)){
                                    $img = !empty($row['photo']) ? $row['photo'] : 'uploads/animal_default.png';
                                    // Giả lập tên Zone (nếu chưa join bảng zone)
                                    $z_id = $row['zone_id'];
                                    $z_name = ($z_id == 1) ? 'Predators' : (($z_id == 2) ? 'Giants' : 'Others');
                                ?>
                                <tr>
                                    <td><img src="<?= $img ?>" style="width:40px; height:40px; border-radius:5px; object-fit:cover;"></td>
                                    <td><strong><?= $row['AName'] ?></strong></td>
                                    <td><?= $row['Species'] ?></td>
                                    <td><span class="badge badge-info"><?= $z_name ?></span></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <img src="uploads/admin_welcome.svg" style="width:60%; opacity:0.8; margin-bottom:20px;" onerror="this.style.display='none'">
                        <h5>Welcome back!</h5>
                        <p class="text-muted">Manage your zoo efficiently using the sidebar menu.</p>
                        <a href="admin_add_animal.php" class="btn btn-success btn-block rounded-pill font-weight-bold">
                            <i class="fas fa-plus"></i> Add New Animal
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>