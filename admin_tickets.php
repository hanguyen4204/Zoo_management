<?php
session_start();
include "connection.php";

// ================= CẤU HÌNH GIÁ VÉ (ĐỂ TÍNH DOANH THU) =================
$ticket_price_per_person = 80000; // Giả sử giá vé là $30 (hoặc 300000 VND)

// 1. CHECK QUYỀN ADMIN
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit(); }
$current_id = $_SESSION['id_user'];
$check_admin = mysqli_query($link, "SELECT role FROM users WHERE id_user = '$current_id'");
$user_data = mysqli_fetch_array($check_admin);
if ($user_data['role'] != 'admin') { header("Location: homescreen.php"); exit(); }

// 2. XỬ LÝ XÓA VÉ
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($link, "DELETE FROM ticket_orders WHERE id_order = $id");
    header("Location: admin_tickets.php?msg=deleted");
    exit();
}

// 3. THỐNG KÊ NHANH (DASHBOARD MINI)
$today = date('Y-m-d');

// Tổng vé đã bán (Dựa vào quantity)
$total_qty_query = mysqli_query($link, "SELECT SUM(quantity) as total FROM ticket_orders");
$total_tickets_sold = mysqli_fetch_assoc($total_qty_query)['total'] ?? 0;

// Doanh thu ước tính (Số vé * Giá vé)
$total_revenue = $total_tickets_sold * $ticket_price_per_person;

// Số khách tham quan HÔM NAY (visit_date = hôm nay)
$today_visitors_query = mysqli_query($link, "SELECT SUM(quantity) as total FROM ticket_orders WHERE visit_date = '$today'");
$today_visitors = mysqli_fetch_assoc($today_visitors_query)['total'] ?? 0;

// 4. LẤY DANH SÁCH VÉ & TÌM KIẾM
$search_term = "";
$where_clause = "";

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_term = mysqli_real_escape_string($link, $_GET['q']);
    // Tìm theo Tên, Email, SĐT hoặc Mã đơn
    $where_clause = " WHERE name LIKE '%$search_term%' 
                      OR email LIKE '%$search_term%' 
                      OR phone LIKE '%$search_term%' 
                      OR id_order LIKE '%$search_term%' ";
}

// Lấy dữ liệu (Sắp xếp ngày tham quan mới nhất lên đầu)
$sql = "SELECT * FROM ticket_orders 
        $where_clause
        ORDER BY visit_date DESC, order_date DESC";

$res = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tickets | Zoo Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        
        /* Sidebar Styles */
        .sidebar { height: 100vh; width: 250px; position: fixed; top: 0; left: 0; background: #0b3d2e; color: #fff; z-index: 1000; }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { color: #f4f91d; margin:0; font-weight:800; }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a { padding: 15px 25px; display: block; color: #ccc; text-decoration: none; font-weight: 600; border-left: 4px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.05); color: #fff; border-left-color: #f4f91d; }
        .sidebar-menu i { width: 25px; }
        .main-content { margin-left: 250px; padding: 20px; }

        /* Stats Cards */
        .stat-card { background: #fff; border-radius: 10px; padding: 20px; display: flex; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 5px solid #0b3d2e; }
        .stat-icon { font-size: 30px; margin-right: 15px; color: #0b3d2e; width: 50px; height: 50px; background: #e0f2f1; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .stat-info h4 { margin: 0; font-weight: 800; font-size: 24px; }
        .stat-info p { margin: 0; color: #777; font-size: 13px; text-transform: uppercase; }

        /* Table & Badges */
        .table-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .ticket-id { font-family: monospace; font-weight: bold; color: #0b3d2e; background: #e0f2f1; padding: 2px 6px; border-radius: 4px; }
        .date-badge { font-weight: 600; color: #555; }
        .today-badge { color: #e74c3c; font-weight: bold; animation: pulse 2s infinite; }
        
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header"><h3>🦁 ZOO ADMIN</h3></div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_animals.php"><i class="fas fa-paw"></i> Quản lý Thú</a>
            <a href="admin_zones.php"><i class="fas fa-map-marked-alt"></i> Khu vực (Zones)</a>
            <a href="admin_activities.php"><i class="fas fa-calendar-alt"></i> Sự kiện</a>
            <a href="admin_tickets.php" class="active"><i class="fas fa-ticket-alt"></i> Vé đặt</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Người dùng</a>
            <a href="admin_social.php"><i class="fas fa-comments"></i> Mạng xã hội</a>
            <a href="homescreen.php" target="_blank" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1)"><i class="fas fa-external-link-alt"></i> Xem trang chủ</a>
            <a href="logout.php" class="text-danger mt-3"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>

    <div class="main-content">
        
        <h2 class="text-dark font-weight-bold mb-4">Quản Lý Vé Đặt</h2>

        <div class="row">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-info">
                        <h4>$<?= number_format($total_revenue, 0) ?></h4>
                        <p>Doanh Thu Ước Tính</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" style="border-left-color: #f4f91d;">
                    <div class="stat-icon" style="color:#fbc02d; background:#fff9c4;"><i class="fas fa-ticket-alt"></i></div>
                    <div class="stat-info">
                        <h4><?= $total_tickets_sold ?></h4>
                        <p>Tổng Vé Đã Bán</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" style="border-left-color: #e74c3c;">
                    <div class="stat-icon" style="color:#e74c3c; background:#ffebee;"><i class="fas fa-walking"></i></div>
                    <div class="stat-info">
                        <h4><?= $today_visitors ?></h4>
                        <p>Khách Đến Hôm Nay</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
            <div class="alert alert-success">Đã xóa đơn đặt vé thành công!</div>
        <?php endif; ?>

        <div class="table-card">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <form action="" method="GET" class="d-flex">
                        <input type="text" name="q" class="form-control" placeholder="Tìm tên, SĐT, Email hoặc Mã đơn..." value="<?= htmlspecialchars($search_term) ?>">
                        <button class="btn btn-primary ml-1"><i class="fas fa-search"></i></button>
                        <?php if(!empty($search_term)): ?>
                            <a href="admin_tickets.php" class="btn btn-secondary ml-1"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th>Mã Đơn</th>
                            <th>Thông Tin Khách</th>
                            <th>Ngày Tham Quan</th>
                            <th>Số Lượng</th>
                            <th>Thành Tiền</th>
                            <th>Ngày Đặt Vé</th>
                            <th class="text-center">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($res) > 0) {
                            while($row = mysqli_fetch_array($res)): 
                                // Xử lý hiển thị ngày
                                $visitDateRaw = $row['visit_date'];
                                $isToday = ($visitDateRaw == date('Y-m-d'));
                                $visitDateDisplay = date('d/m/Y', strtotime($visitDateRaw));
                                
                                $orderDateDisplay = date('d/m/Y H:i', strtotime($row['order_date']));
                                
                                // Tính tiền cho đơn này
                                $orderTotal = $row['quantity'] * $ticket_price_per_person;
                        ?>
                        <tr>
                            <td><span class="ticket-id">#<?= str_pad($row['id_order'], 6, '0', STR_PAD_LEFT) ?></span></td>
                            <td>
                                <strong><?= $row['name'] ?></strong><br>
                                <small class="text-muted"><i class="fas fa-phone"></i> <?= $row['phone'] ?></small><br>
                                <small class="text-muted"><i class="fas fa-envelope"></i> <?= $row['email'] ?></small>
                            </td>
                            <td>
                                <?php if($isToday): ?>
                                    <span class="today-badge"><i class="far fa-calendar-check"></i> HÔM NAY</span>
                                <?php else: ?>
                                    <span class="date-badge"><i class="far fa-calendar-alt"></i> <?= $visitDateDisplay ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="font-size: 16px;"><?= $row['quantity'] ?></strong> vé
                            </td>
                            <td class="text-success font-weight-bold">
                                $<?= number_format($orderTotal, 0) ?>
                            </td>
                            <td class="text-muted small">
                                <?= $orderDateDisplay ?>
                            </td>
                            <td class="text-center">
                                <a href="admin_tickets.php?delete_id=<?= $row['id_order'] ?>" class="btn btn-danger btn-sm rounded-circle" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn vé này không?');" title="Xóa vé">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        } else {
                            echo "<tr><td colspan='7' class='text-center py-4 text-muted'>Chưa có dữ liệu đặt vé nào.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>