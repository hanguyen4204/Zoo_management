<?php
session_start();
session_unset();      
session_destroy();    

// Xóa cookies nếu có dùng
setcookie("user_id", "", time() - 3600, "/");
setcookie("role", "", time() - 3600, "/");

// Quay về trang đăng nhập / đăng ký
header("Location: index.php");
exit;
