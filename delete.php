<?php

include "connection.php";

// Bắt session (nếu chưa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Kiểm tra phân quyền: chỉ admin được xóa ---
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Trả 403 nếu không phải admin
    http_response_code(403);
    echo "403 Forbidden — Bạn không có quyền xóa bản ghi.";
    exit;
}

// Lấy id an toàn
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    // id không hợp lệ -> quay lại homescreen
    header("Location: homescreen.php");
    exit;
}

// Thực hiện xóa bằng prepared statement (mysqli)
if (!empty($link) && $stmt = mysqli_prepare($link, "DELETE FROM table1 WHERE id = ?")) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Bạn có thể kiểm tra $ok để log lỗi nếu muốn
}

// Redirect về trang danh sách (homescreen)
header("Location: homescreen.php");
exit;




