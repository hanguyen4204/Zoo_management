<?php
session_start();
include "connection.php";

// Kiểm tra session (Dùng đúng tên biến id_user như bạn đã sửa)
if (!isset($_SESSION['id_user'])) { 
    // Nếu chưa đăng nhập thì chuyển về login hoặc báo lỗi
    header("Location: zoo_social.php");
    exit(); 
}
$current_user_id = $_SESSION['id_user'];

// =======================================================
// 1. XỬ LÝ ĐĂNG BÀI
// =======================================================
if (isset($_POST['action']) && $_POST['action'] == 'post_status') {
    $content = mysqli_real_escape_string($link, $_POST['content']);
    $imagePath = NULL;

    // Xử lý upload ảnh
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        // Tạo tên file unique để không trùng
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $fileName;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $imagePath = $target_file;
        }
    }

    // Chỉ insert nếu có nội dung hoặc có ảnh
    if (!empty($content) || !empty($imagePath)) {
        // Insert vào bảng social_posts
        // Lưu ý: user_id là tên cột trong bảng social_posts, $current_user_id là id lấy từ session
        $sql = "INSERT INTO social_posts (user_id, content, image) VALUES ('$current_user_id', '$content', '$imagePath')";
        
        if (!mysqli_query($link, $sql)) {
            die("Lỗi SQL: " . mysqli_error($link)); // Báo lỗi nếu SQL sai
        }
    }
    
    // QUAN TRỌNG: Sau khi xử lý xong phải chuyển hướng về trang social
    header("Location: zoo_social.php");
    exit();
}

// =======================================================
// 2. XỬ LÝ LIKE (AJAX)
// =======================================================
if (isset($_POST['action']) && $_POST['action'] == 'toggle_like') {
    $post_id = $_POST['post_id'];
    
    $check = mysqli_query($link, "SELECT id FROM social_likes WHERE post_id = $post_id AND user_id = $current_user_id");
    
    if (mysqli_num_rows($check) > 0) {
        // Unlike
        mysqli_query($link, "DELETE FROM social_likes WHERE post_id = $post_id AND user_id = $current_user_id");
        echo "unliked";
    } else {
        // Like
        mysqli_query($link, "INSERT INTO social_likes (post_id, user_id) VALUES ($post_id, $current_user_id)");
        echo "liked";
    }
    exit();
}

// =======================================================
// 3. XỬ LÝ COMMENT (AJAX)
// =======================================================
if (isset($_POST['action']) && $_POST['action'] == 'add_comment') {
    $post_id = $_POST['post_id'];
    $content = mysqli_real_escape_string($link, $_POST['content']);
    
    if(!empty($content)){
        // Insert comment
        mysqli_query($link, "INSERT INTO social_comments (post_id, user_id, content) VALUES ($post_id, $current_user_id, '$content')");
        
        // Lấy lại thông tin user để trả về HTML hiển thị ngay lập tức
        $u_query = mysqli_query($link, "SELECT username, photo FROM users WHERE id_user = $current_user_id");
        $u = mysqli_fetch_array($u_query);
        $avatar = !empty($u['photo']) ? $u['photo'] : 'uploads/default_user.png';
        
        // Trả về HTML của comment vừa tạo
        echo '
        <div class="comment-item">
            <img src="'.$avatar.'" class="comment-avatar">
            <div class="comment-box">
                <span class="comment-name">'.$u['username'].'</span>
                <span class="comment-text">'.$content.'</span>
            </div>
        </div>';
    }
    exit();
}
?>