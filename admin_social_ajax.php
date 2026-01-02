<?php
session_start();
include "connection.php";

// 1. CHECK QUYỀN ADMIN
if (!isset($_SESSION['id_user'])) { exit('login_required'); }
$current_id = $_SESSION['id_user'];
$check_admin = mysqli_query($link, "SELECT role FROM users WHERE id_user = '$current_id'");
$user_data = mysqli_fetch_assoc($check_admin);

if ($user_data['role'] != 'admin') { exit('no_permission'); }

// 2. XỬ LÝ XÓA BÀI VIẾT
if (isset($_POST['action']) && $_POST['action'] == 'delete_post') {
    $post_id = intval($_POST['post_id']);

    // Lấy ảnh để xóa file rác
    $img_query = mysqli_query($link, "SELECT image FROM social_posts WHERE id = $post_id");
    $img_row = mysqli_fetch_assoc($img_query);
    
    if (!empty($img_row['image']) && file_exists($img_row['image'])) {
        unlink($img_row['image']); 
    }

    // Xóa trong DB (Cascade sẽ tự xóa comment/like)
    if(mysqli_query($link, "DELETE FROM social_posts WHERE id = $post_id")){
        echo "deleted";
    } else {
        echo "error";
    }
    exit();
}

// 3. XỬ LÝ XÓA BÌNH LUẬN
if (isset($_POST['action']) && $_POST['action'] == 'delete_comment') {
    $cmt_id = intval($_POST['cmt_id']);
    
    if(mysqli_query($link, "DELETE FROM social_comments WHERE id = $cmt_id")){
        echo "deleted";
    } else {
        echo "error";
    }
    exit();
}
?>