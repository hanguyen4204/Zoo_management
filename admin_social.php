<?php
session_start();
include "connection.php";

// Check quyền Admin
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit(); }
$current_id = $_SESSION['id_user'];
$check_admin = mysqli_query($link, "SELECT role FROM users WHERE id_user = '$current_id' AND role = 'admin'");
if (mysqli_num_rows($check_admin) == 0) { header("Location: homescreen.php"); exit(); }

// Xử lý tìm kiếm
$where_clause = "";
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $q = mysqli_real_escape_string($link, $_GET['q']);
    $where_clause = " WHERE p.content LIKE '%$q%' OR u.username LIKE '%$q%' ";
}

// Lấy danh sách bài viết (Mới nhất lên đầu)
$sql = "SELECT p.*, u.username, u.photo,
        (SELECT COUNT(*) FROM social_likes WHERE post_id = p.id) as likes,
        (SELECT COUNT(*) FROM social_comments WHERE post_id = p.id) as comments
        FROM social_posts p 
        JOIN users u ON p.user_id = u.id_user 
        $where_clause
        ORDER BY p.created_at DESC";
$res = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Cộng Đồng | Zoo Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        
        /* SIDEBAR STYLE (Giống ảnh bạn gửi) */
        .sidebar { height: 100vh; width: 250px; position: fixed; top: 0; left: 0; background: #0b3d2e; color: #fff; z-index: 1000; }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { color: #f4f91d; margin:0; font-weight:800; font-size: 24px; }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a { padding: 15px 25px; display: block; color: #ccc; text-decoration: none; font-weight: 600; border-left: 4px solid transparent; transition: 0.2s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.05); color: #fff; border-left-color: #f4f91d; }
        .sidebar-menu i { width: 25px; margin-right: 10px; }

        /* MAIN CONTENT */
        .main-content { margin-left: 250px; padding: 30px; }
        
        /* CARD QUẢN LÝ BÀI VIẾT */
        .admin-post-card {
            background: #fff; border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 20px; border: 1px solid #e0e0e0;
        }
        .card-header-post {
            padding: 15px; border-bottom: 1px solid #eee;
            display: flex; justify-content: space-between; align-items: center;
            background: #fafafa; border-radius: 8px 8px 0 0;
        }
        .user-info img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; margin-right: 10px; border: 1px solid #ddd; }
        .user-info strong { color: #0b3d2e; font-size: 15px; }
        .post-time { font-size: 12px; color: #888; }
        
        .card-body-post { padding: 15px; font-size: 14px; color: #333; }
        .post-img-thumb { max-width: 150px; max-height: 150px; object-fit: cover; border-radius: 5px; margin-top: 10px; border: 1px solid #eee; }
        
        .card-footer-post {
            padding: 10px 15px; background: #fff; border-top: 1px solid #eee;
            display: flex; justify-content: space-between; align-items: center;
        }
        
        /* Comment Admin View */
        .admin-comments-box {
            background: #f8f9fa; padding: 15px; display: none; /* Ẩn mặc định */
            border-top: 1px solid #eee;
        }
        .adm-cmt-item {
            display: flex; justify-content: space-between; 
            padding: 8px 0; border-bottom: 1px solid #e9ecef; font-size: 13px;
        }
        .adm-cmt-item:last-child { border-bottom: none; }
        .btn-del-mini {
            color: #dc3545; cursor: pointer; padding: 2px 8px; 
            background: #ffebee; border-radius: 4px; font-weight: bold; font-size: 11px;
        }
        .btn-del-mini:hover { background: #dc3545; color: white; }

        /* Nút xóa to */
        .btn-trash {
            background: #dc3545; color: white; border: none;
            width: 35px; height: 35px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            transition: 0.2s;
        }
        .btn-trash:hover { background: #c82333; transform: scale(1.1); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h3>🦁 ZOO ADMIN</h3>
        </div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_animals.php"><i class="fas fa-paw"></i> Quản lý Thú</a>
            <a href="admin_zones.php"><i class="fas fa-map-marked-alt"></i> Khu vực (Zones)</a>
            <a href="admin_activities.php"><i class="fas fa-calendar-alt"></i> Sự kiện</a>
            <a href="admin_tickets.php"><i class="fas fa-ticket-alt"></i> Vé đặt</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Người dùng</a>            
            <a href="admin_social.php" class="active"><i class="fas fa-comments"></i> Mạng xã hội</a>
            <a href="logout.php" class="text-danger mt-3"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>

    <div class="main-content">
        
        <h2 class="text-dark font-weight-bold mb-4">Quản Lý Bài Đăng & Bình Luận</h2>

        <div class="row mb-4">
            <div class="col-md-6">
                <form action="" method="GET" class="d-flex">
                    <input type="text" name="q" class="form-control" placeholder="Tìm nội dung bài viết hoặc tên người đăng..." style="height: 45px;">
                    <button class="btn btn-primary ml-2" style="height: 45px; width: 50px;"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>

        <div class="row">
            <?php while($post = mysqli_fetch_array($res)): 
                $u_avatar = !empty($post['photo']) ? $post['photo'] : 'uploads/default_user.png';
            ?>
            <div class="col-md-12" id="post-card-<?= $post['id'] ?>">
                <div class="admin-post-card">
                    
                    <div class="card-header-post">
                        <div class="user-info d-flex align-items-center">
                            <img src="<?= $u_avatar ?>">
                            <div>
                                <strong><?= $post['username'] ?></strong><br>
                                <span class="post-time"><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></span>
                            </div>
                        </div>
                        <button class="btn-trash" onclick="deletePost(<?= $post['id'] ?>)" title="Xóa bài viết này">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <div class="card-body-post">
                        <p style="margin-bottom: 5px;"><?= nl2br($post['content']) ?></p>
                        <?php if(!empty($post['image'])): ?>
                            <a href="<?= $post['image'] ?>" target="_blank">
                                <img src="<?= $post['image'] ?>" class="post-img-thumb">
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer-post">
                        <div class="text-muted small">
                            <i class="fas fa-heart text-danger"></i> <?= $post['likes'] ?> &nbsp;|&nbsp; 
                            <i class="fas fa-comment text-primary"></i> <span id="cmt-count-<?= $post['id'] ?>"><?= $post['comments'] ?></span> bình luận
                        </div>
                        <?php if($post['comments'] > 0): ?>
                            <button class="btn btn-sm btn-light border" onclick="$('#admin-cmt-area-<?= $post['id'] ?>').slideToggle()">
                                Quản lý bình luận <i class="fas fa-chevron-down"></i>
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="admin-comments-box" id="admin-cmt-area-<?= $post['id'] ?>">
                        <h6 class="font-weight-bold text-muted mb-3" style="font-size:12px; text-transform:uppercase;">Danh sách bình luận:</h6>
                        <?php 
                            $pid = $post['id'];
                            $cmt_sql = "SELECT c.*, u.username FROM social_comments c JOIN users u ON c.user_id = u.id_user WHERE c.post_id = $pid";
                            $cmt_res = mysqli_query($link, $cmt_sql);
                            while($cmt = mysqli_fetch_array($cmt_res)):
                        ?>
                        <div class="adm-cmt-item" id="cmt-row-<?= $cmt['id'] ?>">
                            <div>
                                <strong class="text-success"><?= $cmt['username'] ?>:</strong> 
                                <?= $cmt['content'] ?>
                            </div>
                            <div class="btn-del-mini" onclick="deleteComment(<?= $cmt['id'] ?>, <?= $post['id'] ?>)">
                                Xóa
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>

                </div>
            </div>
            <?php endwhile; ?>
        </div>

    </div>

    <script>
    function deletePost(post_id) {
        if(confirm("CẢNH BÁO: Bạn có chắc muốn xóa bài viết này? Hành động này sẽ xóa vĩnh viễn nội dung, ảnh và các bình luận liên quan.")) {
            $.ajax({
                url: 'admin_social_ajax.php',
                type: 'POST',
                data: { 'action': 'delete_post', 'post_id': post_id },
                success: function(response) {
                    if(response.trim() == 'deleted') {
                        $('#post-card-' + post_id).fadeOut(300, function(){ $(this).remove(); });
                    } else {
                        alert("Lỗi: Không thể xóa. (Check quyền admin)");
                    }
                }
            });
        }
    }

    function deleteComment(cmt_id, post_id) {
        if(confirm("Xóa bình luận này?")) {
            $.ajax({
                url: 'admin_social_ajax.php',
                type: 'POST',
                data: { 'action': 'delete_comment', 'cmt_id': cmt_id },
                success: function(response) {
                    if(response.trim() == 'deleted') {
                        $('#cmt-row-' + cmt_id).fadeOut();
                        // Giảm số đếm
                        var countSpan = $('#cmt-count-' + post_id);
                        countSpan.text(parseInt(countSpan.text()) - 1);
                    }
                }
            });
        }
    }
    </script>

</body>
</html>