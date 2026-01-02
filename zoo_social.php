<?php
session_start();
include "connection.php";

// --- 1. KIỂM TRA ĐĂNG NHẬP CHẶT CHẼ ---
if (!isset($_SESSION['id_user'])) {
    // Nếu chưa có session, chuyển hướng về trang login ngay
    header("Location: login.php"); 
    exit();
}
$current_user_id = $_SESSION['id_user'];

// --- 2. LẤY THÔNG TIN USER HIỆN TẠI ---
// Lấy thông tin mới nhất từ DB để đảm bảo Role và Avatar luôn đúng
$curr_user_query = mysqli_query($link, "SELECT * FROM users WHERE id_user = '$current_user_id'");
$current_user = mysqli_fetch_array($curr_user_query);

// Nếu không tìm thấy user trong DB (trường hợp hiếm), cho đăng xuất luôn
if (!$current_user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Xử lý avatar: Nếu cột photo rỗng hoặc file không tồn tại -> dùng ảnh mặc định
$my_avatar = (!empty($current_user['photo'])) ? $current_user['photo'] : 'uploads/default_user.png';
$my_name = $current_user['username'];

// --- 3. HÀM XỬ LÝ THỜI GIAN (FIX LỖI WARNING) ---
function time_elapsed_string($datetime, $full = false) {
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);

    $string = array(
        'y' => array('val' => $diff->y, 'label' => 'năm'),
        'm' => array('val' => $diff->m, 'label' => 'tháng'),
        'w' => array('val' => $weeks,   'label' => 'tuần'),
        'd' => array('val' => $days,    'label' => 'ngày'),
        'h' => array('val' => $diff->h, 'label' => 'giờ'),
        'i' => array('val' => $diff->i, 'label' => 'phút'),
        's' => array('val' => $diff->s, 'label' => 'giây'),
    );

    $result = array();
    foreach ($string as $k => $v) {
        if ($v['val'] > 0) {
            $result[] = $v['val'] . ' ' . $v['label'];
        }
    }

    if (!$full) $result = array_slice($result, 0, 1);
    return $result ? implode(', ', $result) . ' trước' : 'Vừa xong';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cộng Đồng Zoo Explorer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <style>
        body { background-color: #f4f6f4; font-family: 'Segoe UI', sans-serif; }

        /* User Menu Dropdown */
        .user-menu { position: relative; }
        .user-menu:hover .user-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
        .user-avatar-nav {
            width: 40px; height: 40px; border-radius: 50%;
            background: #0b3d2e; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; cursor: pointer; user-select: none;
            overflow: hidden; /* Để ảnh không bị tràn */
        }
        .user-dropdown {
            position: absolute; top: 52px; right: 0; width: 220px;
            background: #fff; border-radius: 14px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15); padding: 10px 0;
            opacity: 0; visibility: hidden; transform: translateY(10px);
            transition: 0.25s ease; z-index: 2000;
        }
        .user-dropdown a { display: block; padding: 12px 20px; color: #222; text-decoration: none; font-weight: 600; }
        .user-dropdown a:hover { background: #f4f6f4; color: #0b3d2e; }

        /* HEADER STYLE */
        .zoo-header { background: #fff; padding: 15px 0; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 1000; }
        .zoo-brand { color: #0b3d2e; font-weight: 800; font-size: 24px; text-decoration: none; }
        .zoo-brand:hover { color: #0b3d2e; text-decoration: none; }
        .zoo-nav { display: flex; align-items: center; justify-content: space-between; }
        .zoo-menu { display: flex; gap: 20px; }
        .menu-item > a { text-decoration: none; color: #222; font-weight: 600; }

        .buy-ticket {
            background: #f4f91d; color: #0b3d2e; font-weight: 700;
            padding: 8px 20px; border-radius: 999px; text-decoration: none; transition: 0.25s ease;
        }
        .buy-ticket:hover { background: #e6eb00; text-decoration: none; color: #0b3d2e; }
        
        /* FEED CONTAINER */
        .feed-container { max-width: 700px; margin: 30px auto; padding: 0 15px; }

        /* CREATE POST BUTTON */
        .create-post-section {
            background: #fff; border-radius: 12px; padding: 20px;
            margin-bottom: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex; align-items: center; justify-content: space-between;
        }
        .btn-create {
            background: #0b3d2e; color: #fff; font-weight: 600;
            padding: 10px 25px; border-radius: 30px; text-decoration: none; transition: 0.3s;
        }
        .btn-create:hover { background: #f4f91d; color: #0b3d2e; text-decoration: none; }

        /* POST CARD */
        .post-card {
            background: #fff; border-radius: 12px;
            margin-bottom: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden; border: 1px solid #eee;
        }
        
        .post-header { padding: 15px; display: flex; align-items: center; }
        .user-avatar-post { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; margin-right: 12px; border: 2px solid #f4f6f4; }
        .user-info h5 { margin: 0; font-size: 16px; font-weight: 700; color: #333; }
        .user-info span { font-size: 12px; color: #888; }

        /* Admin Badge */
        .admin-badge {
            background: #0b3d2e; color: #f4f91d; font-size: 10px;
            padding: 2px 8px; border-radius: 4px; font-weight: bold;
            margin-left: 5px; text-transform: uppercase;
        }

        .post-content { padding: 0 15px 15px 15px; color: #333; font-size: 15px; line-height: 1.6; }
        .post-image { width: 100%; display: block; height: auto; object-fit: cover; border-top: 1px solid #f1f1f1; border-bottom: 1px solid #f1f1f1; }

        /* ACTIONS (LIKE/COMMENT) */
        .post-actions {
            padding: 10px 15px; background: #fafafa;
            display: flex; gap: 20px; border-top: 1px solid #eee;
        }
        .action-item { cursor: pointer; color: #555; font-weight: 600; font-size: 14px; transition: 0.2s; }
        .action-item:hover { color: #0b3d2e; }
        .action-item.liked { color: #e74c3c; } /* Màu đỏ tim */
        .action-item i { margin-right: 5px; }

        /* COMMENT SECTION */
        .comment-section { background: #f9f9f9; padding: 15px; display: none; }
        .comment-item { display: flex; margin-bottom: 15px; }
        .comment-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; margin-right: 10px; }
        .comment-box { background: #fff; padding: 8px 12px; border-radius: 12px; border: 1px solid #eee; width: 100%; }
        .comment-name { font-weight: 700; font-size: 13px; color: #0b3d2e; display: block; margin-bottom: 2px; }
        .comment-text { font-size: 14px; color: #555; }

        .input-comment-wrapper { display: flex; gap: 10px; align-items: center; }
        .input-comment { flex-grow: 1; border: 1px solid #ddd; border-radius: 20px; padding: 8px 15px; outline: none; }
    </style>
</head>
<body>

    <header class="zoo-header">
        <div class="container zoo-nav">
            <a href="homescreen.php" class="zoo-brand"><i class="fas fa-paw"></i> Zoo Social</a>
            
            <nav class="zoo-menu d-none d-md-flex">
                <div class="menu-item"><a href="homescreen.php" style="color:#0b3d2e; font-weight:700;">Home</a></div>
            <div class="menu-item"><a href="Animal-display.php">Animals</a></div> 
            <div class="menu-item"><a href="activity.php">Activities</a></div>
            <div class="menu-item"><a href="zone.php">Zones</a></div>
            <div class="menu-item"><a href="zoo_social.php">Community</a></div>
            <div class="menu-item"><a href="about_us.php">About us</a></div>
            </nav>

            <div class="d-flex align-items-center">
                <a href="ticket.php" class="buy-ticket mr-3 d-none d-sm-block">BUY TICKETS</a>
                
                <div class="user-menu">
                    <div class="user-avatar-nav">
                        <img src="<?= $my_avatar ?>" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div class="user-dropdown">
                        <div style="padding: 10px 20px; border-bottom:1px solid #eee; color:#0b3d2e; font-weight:bold;">
                            <?= $my_name ?>
                        </div>
                        <a href="Profile_user.php">👤 Account info</a>
                        <a href="ticket_history.php">🎟 Ticket history</a>
                        <a href="logout.php">🚪 Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="feed-container">
        
        <div class="create-post-section">
            <div>
                <h4 style="margin:0; color:#0b3d2e; font-weight:700;">Bạn đang nghĩ gì?</h4>
                <p style="margin:0; color:#777; font-size:14px;">Chia sẻ khoảnh khắc với cộng đồng.</p>
            </div>
            <a href="social_create_post.php" class="btn-create">
                <i class="fas fa-pen"></i> Viết bài mới
            </a>
        </div>

        <?php
        $sql = "SELECT p.*, 
                u.username, u.photo, u.role,
                (SELECT COUNT(*) FROM social_likes WHERE post_id = p.id) as total_likes,
                (SELECT COUNT(*) FROM social_comments WHERE post_id = p.id) as total_comments,
                (SELECT COUNT(*) FROM social_likes WHERE post_id = p.id AND user_id = $current_user_id) as is_liked
                FROM social_posts p
                JOIN users u ON p.user_id = u.id_user
                ORDER BY p.created_at DESC";
        $res = mysqli_query($link, $sql);

        if(mysqli_num_rows($res) > 0) {
            while ($post = mysqli_fetch_array($res)) {
                $likedClass = ($post['is_liked'] > 0) ? 'liked' : '';
                // Avatar người đăng bài
                $avatar_poster = !empty($post['photo']) ? $post['photo'] : 'uploads/default_user.png';
        ?>
        
        <div class="post-card">
            <div class="post-header">
                <img src="<?= $avatar_poster ?>" class="user-avatar-post">
                <div class="user-info">
                    <div class="d-flex align-items-center">
                        <h5><?= $post['username'] ?></h5>
                        <?php if($post['role'] == 'admin') { ?>
                            <span class="admin-badge"><i class="fas fa-check"></i> Admin</span>
                        <?php } ?>
                    </div>
                    <span><?= time_elapsed_string($post['created_at']) ?></span>
                </div>
            </div>

            <div class="post-content">
                <?= nl2br($post['content']) ?>
            </div>

            <?php if(!empty($post['image'])): ?>
                <img src="<?= $post['image'] ?>" class="post-image">
            <?php endif; ?>

            <div class="post-actions">
                <div class="action-item like-btn <?= $likedClass ?>" data-id="<?= $post['id'] ?>">
                    <i class="<?= ($post['is_liked'] > 0) ? 'fas' : 'far' ?> fa-heart"></i>
                    <span class="count"><?= $post['total_likes'] ?></span> Thích
                </div>
                <div class="action-item" onclick="$('#comment-area-<?= $post['id'] ?>').slideToggle()">
                    <i class="far fa-comment-dots"></i>
                    <span id="comment-count-<?= $post['id'] ?>"><?= $post['total_comments'] ?></span> Bình luận
                </div>
            </div>

            <div class="comment-section" id="comment-area-<?= $post['id'] ?>">
                
                <?php 
                    $pid = $post['id'];
                    $cmt_sql = "SELECT c.*, u.username, u.photo 
                                FROM social_comments c 
                                JOIN users u ON c.user_id = u.id_user 
                                WHERE c.post_id = $pid ORDER BY c.created_at ASC";
                    $cmt_res = mysqli_query($link, $cmt_sql);
                    while($cmt = mysqli_fetch_array($cmt_res)):
                        $c_avatar = !empty($cmt['photo']) ? $cmt['photo'] : 'uploads/default_user.png';
                ?>
                <div class="comment-item">
                    <img src="<?= $c_avatar ?>" class="comment-avatar">
                    <div class="comment-box">
                        <span class="comment-name"><?= $cmt['username'] ?></span>
                        <span class="comment-text"><?= $cmt['content'] ?></span>
                    </div>
                </div>
                <?php endwhile; ?>

                <form class="input-comment-wrapper ajax-comment-form" data-id="<?= $post['id'] ?>">
                    <img src="<?= $my_avatar ?>" class="comment-avatar">
                    <input type="text" name="content" class="input-comment" placeholder="Viết bình luận..." required autocomplete="off">
                    <button type="submit" class="btn btn-sm btn-success ml-2"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>

        <?php 
            } // End While
        } else {
            echo "<div class='text-center py-5'>Chưa có bài viết nào. Hãy là người đầu tiên chia sẻ!</div>";
        }
        ?>

    </div>

    <script>
    $(document).ready(function(){
        
        // --- XỬ LÝ LIKE ---
        $('.like-btn').click(function(){
            var btn = $(this);
            var post_id = btn.data('id');
            var countSpan = btn.find('.count'); 

            $.ajax({
                url: 'social_ajax.php',
                type: 'POST',
                data: { 'action': 'toggle_like', 'post_id': post_id },
                success: function(response){
                    var currentCount = parseInt(countSpan.text());
                    if(response.trim() == 'liked'){
                        btn.addClass('liked');
                        btn.find('i').removeClass('far').addClass('fas');
                        countSpan.text(currentCount + 1);
                    } else if(response.trim() == 'unliked'){
                        btn.removeClass('liked');
                        btn.find('i').removeClass('fas').addClass('far');
                        countSpan.text(currentCount - 1);
                    }
                }
            });
        });

        // --- XỬ LÝ COMMENT ---
        $('.ajax-comment-form').submit(function(e){
            e.preventDefault(); 
            
            var form = $(this);
            var post_id = form.data('id');
            var input = form.find('input[name="content"]');
            var content = input.val();
            
            var countSpan = $('#comment-count-' + post_id);

            $.ajax({
                url: 'social_ajax.php',
                type: 'POST',
                data: { 
                    'action': 'add_comment', 
                    'post_id': post_id, 
                    'content': content 
                },
                success: function(response){
                    // Chèn comment mới vào trước form
                    $(response).insertBefore(form);
                    // Xóa ô nhập
                    input.val('');
                    // Tăng số lượng
                    var currentCount = parseInt(countSpan.text());
                    countSpan.text(currentCount + 1);
                }
            });
        });

    });
    </script>
</body>
</html>