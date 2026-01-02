<?php
session_start();
include "connection.php";

/* Kiểm tra đăng nhập */
if(!isset($_SESSION['id_user'])){
    header("Location: homescreen.php");
    exit;
}

$id_user = $_SESSION['id_user'];

/* Lấy thông tin user */
$res = mysqli_query($link,"
    SELECT username, email, photo
    FROM users
    WHERE id_user = $id_user
");

if(mysqli_num_rows($res) == 0){
    die("User not found");
}

$user = mysqli_fetch_assoc($res);

/* Avatar mặc định nếu chưa có ảnh */
$avatar = !empty($user['photo']) 
          ? $user['photo'] 
          : "uploads/default-avatar.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<style>
body{
    background:#f4f6f4;
    font-family:'Segoe UI',sans-serif;
}

/* PROFILE CARD */
.profile-box{
    max-width:480px;
    margin:100px auto;
    background:#fff;
    padding:50px 40px;
    border-radius:20px;
    box-shadow:0 12px 30px rgba(0,0,0,0.12);
    text-align:center;
}

/* AVATAR */
.avatar{
    width:140px;
    height:140px;
    border-radius:50%;
    object-fit:cover;
    border:5px solid #f4f91d;
    margin-bottom:20px;
}

/* NAME */
.profile-box h2{
    font-weight:800;
    color:#0b3d2e;
    margin-bottom:10px;
}

/* EMAIL */
.profile-box p{
    color:#555;
    font-size:16px;
    margin-bottom:30px;
}

/* BUTTONS */
.profile-actions{
    display:flex;
    gap:14px;
    justify-content:center;
}

.btn-edit{
    background:#f4f91d;
    color:#0b3d2e;
    font-weight:800;
    padding:12px 26px;
    border-radius:999px;
    text-decoration:none;
}
.btn-edit:hover{
    background:#e6eb00;
    text-decoration:none;
    color:#0b3d2e;
}

.btn-back{
    border:2px solid #0b3d2e;
    color:#0b3d2e;
    font-weight:700;
    padding:12px 26px;
    border-radius:999px;
    text-decoration:none;
}
.btn-back:hover{
    background:#0b3d2e;
    color:#fff;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="profile-box">

    <!-- AVATAR -->
    <img src="<?= htmlspecialchars($avatar) ?>" class="avatar" alt="User Avatar">

    <!-- NAME -->
    <h2><?= htmlspecialchars($user['username']) ?></h2>

    <!-- EMAIL -->
    <p><?= htmlspecialchars($user['email']) ?></p>

    <!-- ACTIONS -->
    <div class="profile-actions">
        <a href="edit_profile.php" class="btn-edit">✏️ Edit profile</a>
        <a href="homescreen.php" class="btn-back">← Back</a>
    </div>

</div>

</body>
</html>
