<?php
session_start();
include "connection.php";

// 1. CHECK QUYỀN ADMIN
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit(); }
$current_id = $_SESSION['id_user'];
$check_admin = mysqli_query($link, "SELECT role FROM users WHERE id_user = '$current_id'");
$user_data = mysqli_fetch_array($check_admin);
if ($user_data['role'] != 'admin') { header("Location: homescreen.php"); exit(); }

// 2. XỬ LÝ XÓA USER
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    // BẢO VỆ: Không cho phép xóa chính mình
    if ($id == $current_id) {
        $error = "Bạn không thể xóa tài khoản của chính mình khi đang đăng nhập!";
    } else {
        // Vì trong database bạn đã set ON DELETE CASCADE cho vé, 
        // nên xóa User sẽ tự động xóa vé của họ. Rất tiện!
        mysqli_query($link, "DELETE FROM users WHERE id_user = $id");
        header("Location: admin_users.php?msg=deleted");
        exit();
    }
}

// 3. XỬ LÝ ĐỔI QUYỀN (ROLE)
if (isset($_GET['role_id']) && isset($_GET['new_role'])) {
    $id = intval($_GET['role_id']);
    $new_role = ($_GET['new_role'] == 'admin') ? 'admin' : 'user'; // Chỉ nhận 2 giá trị này
    
    if ($id == $current_id) {
        $error = "Bạn không thể tự giáng chức chính mình!";
    } else {
        mysqli_query($link, "UPDATE users SET role = '$new_role' WHERE id_user = $id");
        header("Location: admin_users.php?msg=updated");
        exit();
    }
}

// 4. LẤY DANH SÁCH & TÌM KIẾM
$search_term = "";
$where_clause = "";

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_term = mysqli_real_escape_string($link, $_GET['q']);
    $where_clause = " WHERE username LIKE '%$search_term%' OR email LIKE '%$search_term%' ";
}

$sql = "SELECT * FROM users $where_clause ORDER BY role ASC, id_user DESC"; 
// Sắp xếp: Admin lên đầu, sau đó đến người mới đăng ký
$res = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | Zoo Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        
        /* Sidebar (Giữ nguyên) */
        .sidebar { height: 100vh; width: 250px; position: fixed; top: 0; left: 0; background: #0b3d2e; color: #fff; z-index: 1000; }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { color: #f4f91d; margin:0; font-weight:800; }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a { padding: 15px 25px; display: block; color: #ccc; text-decoration: none; font-weight: 600; border-left: 4px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.05); color: #fff; border-left-color: #f4f91d; }
        .sidebar-menu i { width: 25px; }
        .main-content { margin-left: 250px; padding: 20px; }

        /* User Table Styles */
        .table-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .user-avatar-sm { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #eee; margin-right: 10px; }
        
        /* Badge Role */
        .badge-role { padding: 6px 12px; border-radius: 20px; font-size: 11px; text-transform: uppercase; font-weight: 700; }
        .role-admin { background: #fce4ec; color: #c2185b; border: 1px solid #f8bbd0; } /* Màu hồng đậm */
        .role-user { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; } /* Màu xanh lá */

        /* Buttons */
        .btn-action { width: 32px; height: 32px; padding: 0; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin: 0 2px; }
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
            <a href="admin_tickets.php"><i class="fas fa-ticket-alt"></i> Vé đặt</a>
            <a href="admin_users.php" class="active"><i class="fas fa-users"></i> Người dùng</a>
            <a href="admin_social.php"><i class="fas fa-comments"></i> Mạng xã hội</a>
            <a href="homescreen.php" target="_blank" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1)"><i class="fas fa-external-link-alt"></i> Xem trang chủ</a>
            <a href="logout.php" class="text-danger mt-3"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>

    <div class="main-content">
        
        <h2 class="text-dark font-weight-bold mb-4">Quản Lý Người Dùng</h2>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
        <?php endif; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
            <div class="alert alert-success">Đã xóa người dùng thành công!</div>
        <?php endif; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg']=='updated'): ?>
            <div class="alert alert-info">Đã cập nhật phân quyền thành công!</div>
        <?php endif; ?>

        <div class="table-card">
            
            <div class="row mb-3">
                <div class="col-md-5">
                    <form action="" method="GET" class="d-flex">
                        <input type="text" name="q" class="form-control" placeholder="Tìm theo tên hoặc email..." value="<?= htmlspecialchars($search_term) ?>">
                        <button class="btn btn-primary ml-1"><i class="fas fa-search"></i></button>
                        <?php if(!empty($search_term)): ?>
                            <a href="admin_users.php" class="btn btn-secondary ml-1"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Thành viên</th>
                            <th>Thông tin liên hệ</th>
                            <th>Vai trò (Role)</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_array($res)): 
                            $avatar = !empty($row['photo']) ? $row['photo'] : 'uploads/default_user.png';
                            $isAdmin = ($row['role'] == 'admin');
                            $roleClass = $isAdmin ? 'role-admin' : 'role-user';
                            $roleLabel = $isAdmin ? 'Admin (Quản trị)' : 'Member (Khách)';
                        ?>
                        <tr style="<?= ($row['id_user'] == $current_id) ? 'background-color: #fff8e1;' : '' ?>">
                            <td>#<?= $row['id_user'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?= $avatar ?>" class="user-avatar-sm">
                                    <div>
                                        <strong><?= $row['username'] ?></strong>
                                        <?php if($row['id_user'] == $current_id) echo '<br><small class="text-muted">(Bạn)</small>'; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-size:13px;">
                                    <i class="fas fa-envelope text-muted" style="width:15px;"></i> <?= $row['email'] ?><br>
                                    <i class="fas fa-phone text-muted" style="width:15px;"></i> <?= !empty($row['phone']) ? $row['phone'] : '---' ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-role <?= $roleClass ?>"><?= $roleLabel ?></span>
                            </td>
                            <td class="text-center">
                                <?php if($row['id_user'] != $current_id): // Không cho thao tác trên chính mình ?>
                                    
                                    <?php if($isAdmin): ?>
                                        <a href="admin_users.php?role_id=<?= $row['id_user'] ?>&new_role=user" class="btn btn-warning btn-action text-white" title="Giáng xuống thành viên" onclick="return confirm('Bạn muốn giáng chức người này xuống thành User thường?');">
                                            <i class="fas fa-arrow-down"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="admin_users.php?role_id=<?= $row['id_user'] ?>&new_role=admin" class="btn btn-success btn-action" title="Thăng lên Admin" onclick="return confirm('CẢNH BÁO: Bạn muốn cấp quyền Admin cho người này? Họ sẽ có toàn quyền quản lý hệ thống.');">
                                            <i class="fas fa-arrow-up"></i>
                                        </a>
                                    <?php endif; ?>

                                    <a href="admin_users.php?delete_id=<?= $row['id_user'] ?>" class="btn btn-danger btn-action" title="Xóa tài khoản" onclick="return confirm('Xóa người dùng này sẽ xóa luôn Lịch sử đặt vé và Bài đăng MXH của họ. Bạn có chắc không?');">
                                        <i class="fas fa-trash"></i>
                                    </a>

                                <?php else: ?>
                                    <span class="text-muted small">Đang truy cập</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>