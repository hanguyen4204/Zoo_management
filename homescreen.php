<?php
session_start();
include "connection.php";

// ================= 1. LOGIC LẤY THÔNG TIN USER =================
$my_avatar = 'uploads/default_user.png'; // Ảnh mặc định
$my_name = 'Guest';

// Kiểm tra login
if (isset($_SESSION['id_user'])) {
    $current_user_id = $_SESSION['id_user'];
    
    // Query lấy thông tin user mới nhất
    $user_query = mysqli_query($link, "SELECT * FROM users WHERE id_user = '$current_user_id'");
    
    if ($current_user = mysqli_fetch_array($user_query)) {
        $my_name = $current_user['username'];
        if (!empty($current_user['photo'])) {
            $my_avatar = $current_user['photo'];
        }
    }
}

// ================= 2. LOGIC LẤY DANH SÁCH ĐỘNG VẬT =================
$res = mysqli_query($link, "SELECT * FROM table1");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Zoo Explorer</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
/* ================= GLOBAL ================= */
body{ margin:0; font-family:'Segoe UI',sans-serif; background:#f4f6f4; }

/* ================= HEADER STYLE (Đã cập nhật) ================= */
.zoo-header { background: #fff; padding: 15px 0; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 1000; }
.zoo-nav { display: flex; align-items: center; justify-content: space-between; height: auto; } /* Auto height để linh hoạt */
.zoo-logo { font-size: 24px; font-weight: 800; color: #0b3d2e; text-decoration: none; }
.zoo-logo:hover { color: #0b3d2e; text-decoration: none; }

/* MENU MỚI (Đơn giản, không có Mega Panel) */
.zoo-menu { display: flex; gap: 30px; }
.menu-item > a { 
    text-decoration: none; 
    color: #222; 
    font-weight: 600; 
    font-size: 16px;
    padding: 10px 0; 
    display: inline-block;
    transition: 0.2s;
}
.menu-item > a:hover { color: #0b3d2e; }

/* RIGHT ACTIONS */
.zoo-actions { display: flex; align-items: center; gap: 20px; }

.buy-ticket {
    background: #f4f91d; color: #0b3d2e; font-weight: 700;
    padding: 10px 25px; border-radius: 999px; text-decoration: none;
    transition: 0.25s ease; white-space: nowrap;
}
.buy-ticket:hover { background: #e6eb00; text-decoration: none; color: #0b3d2e; }

/* USER AVATAR & DROPDOWN */
.user-menu { position: relative; }
.user-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: #0b3d2e; color: #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; user-select: none;
    overflow: hidden; /* Cắt ảnh tròn */
    border: 2px solid #eee;
}
.user-avatar img { width: 100%; height: 100%; object-fit: cover; }

.user-dropdown {
    position: absolute; top: 52px; right: 0; width: 240px;
    background: #fff; border-radius: 14px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.15); padding: 10px 0;
    opacity: 0; visibility: hidden; transform: translateY(10px);
    transition: 0.25s ease; z-index: 2000;
}
.user-menu:hover .user-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }

.user-dropdown a { display: block; padding: 12px 20px; color: #222; text-decoration: none; font-weight: 600; }
.user-dropdown a:hover { background: #f4f6f4; color: #0b3d2e; }

/* ================= HERO SECTION (Giữ nguyên) ================= */
.hero{
    position:relative; min-height:85vh;
    background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.45) 35%, rgba(0,0,0,0.15) 65%, rgba(0,0,0,0.05) 100%),
    url("uploads/Screenshot 2025-11-08 160924.png") center/cover no-repeat;
    display:flex; align-items:center; justify-content:center; text-align:center; color:#fff;
}
.hero-content{ max-width:1100px; padding:0 20px; }
.hero h1{ font-size:clamp(48px, 8vw, 120px); font-weight:900; letter-spacing:2px; text-transform:uppercase; margin-bottom:24px; line-height:1.05; }
.hero p{ font-size:clamp(16px, 2.2vw, 22px); font-weight:600; max-width:820px; margin:0 auto 36px; line-height:1.5; }
.hero-actions{ display:flex; gap:18px; justify-content:center; flex-wrap:wrap; }
.hero-btn{ padding:14px 34px; border-radius:999px; font-weight:800; text-decoration:none; font-size:15px; transition:0.25s ease; letter-spacing:0.5px; }
.hero-btn.primary{ background:#f4f91d; color:#0b3d2e; }
.hero-btn.primary:hover{ background:#e6eb00; }
.hero-btn.secondary{ background:#ffffff; color:#0b3d2e; }
.hero-btn.secondary:hover{ background:#f1f1f1; }

/* ================= ANIMAL CARDS ================= */
.animal-card{ background:#fff; border-radius:16px; box-shadow:0 10px 25px rgba(0,0,0,0.08); margin-bottom:30px; overflow:hidden; display:flex; flex-direction:column; height:100%; }
.animal-card img{ width:100%; height:200px; object-fit:cover; }
.animal-info{ padding:16px; display:flex; flex-direction:column; flex-grow:1; }
.animal-info h4{ color:#0b3d2e; font-weight:700; margin-top: 0; }
.desc{ color:#555; line-height:1.5; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
.animal-info a{ margin-top:auto; font-weight:600; color:#0b3d2e; }

footer{ background:#0b3d2e; color:white; padding:30px 0; margin-top:60px; }
</style>
</head>

<body>

<header class="zoo-header">
    <div class="container zoo-nav">
        <a href="homescreen.php" class="zoo-logo">🐾 Zoo Explorer</a>

        <nav class="zoo-menu hidden-xs hidden-sm">
            <div class="menu-item"><a href="homescreen.php" style="color:#0b3d2e; font-weight:700;">Home</a></div>
            <div class="menu-item"><a href="Animal-display.php">Animals</a></div> 
            <div class="menu-item"><a href="activity.php">Activities</a></div>
            <div class="menu-item"><a href="zone.php">Zones</a></div>
            <div class="menu-item"><a href="zoo_social.php">Community</a></div>
            <div class="menu-item"><a href="about_us.php">About us</a></div>
        </nav>

        <div class="zoo-actions">
            <a href="ticket.php" class="buy-ticket hidden-xs">BUY TICKETS</a>

            <div class="user-menu">
                <div class="user-avatar">
                    <img src="<?= $my_avatar ?>" alt="Avatar">
                </div>

                <div class="user-dropdown">
                    <div style="padding: 10px 20px; border-bottom:1px solid #eee; color:#0b3d2e; font-weight:bold;">
                        Hello, <?= $my_name ?>
                    </div>
                    <a href="Profile_user.php"><i class="fas fa-user-circle"></i> Account info</a>
                    <a href="ticket_history.php"><i class="fas fa-ticket-alt"></i> Ticket history</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h1>
            ONE TICKET.<br>
            TWO VISITS.
        </h1>
        <p>
            Our Christmas gift to you – visit in December and you can
            come back again for free between 5 Jan – 12 Feb 2026.
        </p>
        <div class="hero-actions">
            <a href="tickets.php" class="hero-btn primary">BOOK TICKETS</a>
            <a href="animals.php" class="hero-btn secondary">EXPLORE OUR ZOO</a>
        </div>
    </div>
</section>

<div class="container" style="margin-top:60px;">
    <div class="row" style="margin-bottom: 30px;">
        <div class="col-md-12 text-center">
            <h2 style="color:#0b3d2e; font-weight:800; margin-bottom:10px;">Meet Our Animals</h2>
            <p style="color:#666;">Discover the amazing wildlife we care for everyday.</p>
        </div>
    </div>

    <div class="row">
    <?php while($row=mysqli_fetch_array($res)){
        $img = !empty($row["photo"]) ? $row["photo"] : "uploads/default.png";
    ?>
    <div class="col-md-4 col-sm-6">
        <div class="animal-card">
            <img src="<?= $img ?>">
            <div class="animal-info">
                <h4><?= $row["AName"] ?></h4>
                <p class="desc"><?= $row["des"] ?></p>
                <a href="view_animal_user.php?id=<?= $row["id"] ?>">View details <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <?php } ?>
    </div>
</div>

<footer class="text-center">
    <p>© 2026 Zoo Explorer – Educational Project</p>
</footer>

</body>
</html>
