<?php
session_start();
include "connection.php";

// ================= 1. LOGIC USER (HEADER) =================
$my_avatar = 'uploads/default_user.png';
$my_name = 'Guest';

if (isset($_SESSION['id_user'])) {
    $current_user_id = $_SESSION['id_user'];
    $user_query = mysqli_query($link, "SELECT * FROM users WHERE id_user = '$current_user_id'");
    if ($current_user = mysqli_fetch_array($user_query)) {
        $my_name = $current_user['username'];
        if (!empty($current_user['photo'])) {
            $my_avatar = $current_user['photo'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>About Us | Zoo Explorer</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<style>
    /* GLOBAL */
    body { background-color: #f4f6f4; font-family: 'Segoe UI', sans-serif; color: #333; }

    /* HEADER (Giữ nguyên) */
    .zoo-header { background: #fff; padding: 15px 0; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 1000; }
    .zoo-nav { display: flex; align-items: center; justify-content: space-between; }
    .zoo-logo { font-size: 24px; font-weight: 800; color: #0b3d2e; text-decoration: none; }
    .zoo-logo:hover { color: #0b3d2e; text-decoration: none; }
    .zoo-menu { display: flex; gap: 30px; }
    .menu-item > a { text-decoration: none; color: #222; font-weight: 600; font-size: 16px; padding: 10px 0; transition: 0.2s; }
    .menu-item > a:hover { color: #0b3d2e; }
    .zoo-actions { display: flex; align-items: center; gap: 20px; }
    .buy-ticket { background: #f4f91d; color: #0b3d2e; font-weight: 700; padding: 10px 25px; border-radius: 999px; text-decoration: none; transition: 0.25s ease; white-space: nowrap; }
    .buy-ticket:hover { background: #e6eb00; text-decoration: none; color: #0b3d2e; }

    /* USER AVATAR */
    .user-menu { position: relative; }
    .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: #0b3d2e; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; border: 2px solid #eee; }
    .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .user-dropdown { position: absolute; top: 52px; right: 0; width: 240px; background: #fff; border-radius: 14px; box-shadow: 0 15px 35px rgba(0,0,0,0.15); padding: 10px 0; opacity: 0; visibility: hidden; transform: translateY(10px); transition: 0.25s ease; z-index: 2000; }
    .user-menu:hover .user-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
    .user-dropdown a { display: block; padding: 12px 20px; color: #222; text-decoration: none; font-weight: 600; }
    .user-dropdown a:hover { background: #f4f6f4; color: #0b3d2e; }

    /* ================= ABOUT US STYLES ================= */
    
    /* 1. HERO BANNER */
    .about-hero {
        position: relative; height: 50vh;
        /* Gợi ý ảnh: Toàn cảnh sở thú */
        background: linear-gradient(rgba(11, 61, 46, 0.7), rgba(11, 61, 46, 0.7)), url('uploads/1767315205_Screenshot 2025-11-18 122110.png') center/cover fixed;
        display: flex; align-items: center; justify-content: center; text-align: center; color: white;
    }
    .about-hero h1 { font-size: 4rem; font-weight: 900; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 20px; }
    .about-hero p { font-size: 1.2rem; max-width: 700px; margin: 0 auto; opacity: 0.9; }

    /* 2. INTRO SECTION */
    .intro-section { padding: 80px 0; }
    .intro-img { 
        width: 100%; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.1); 
        transition: transform 0.3s;
    }
    .intro-img:hover { transform: scale(1.02); }
    .section-title { color: #0b3d2e; font-weight: 800; margin-bottom: 25px; position: relative; display: inline-block; }
    .section-title::after { content: ''; display: block; width: 60px; height: 4px; background: #f4f91d; margin-top: 10px; }

    /* 3. MISSION CARDS */
    .mission-section { background: #fff; padding: 80px 0; }
    .mission-card {
        background: #f4f6f4; padding: 40px 30px; border-radius: 15px;
        text-align: center; height: 100%; transition: 0.3s;
        border-bottom: 5px solid transparent;
    }
    .mission-card:hover { transform: translateY(-10px); border-bottom-color: #f4f91d; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .mission-icon { font-size: 50px; color: #0b3d2e; margin-bottom: 20px; }
    .mission-card h4 { font-weight: 700; margin-bottom: 15px; }

    /* 4. STATS BANNER */
    .stats-section {
        background: #0b3d2e; color: white; padding: 60px 0; text-align: center;
    }
    .stat-number { font-size: 3rem; font-weight: 800; color: #f4f91d; }
    .stat-label { font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }

    footer { background:#0b3d2e; color:white; padding:30px 0; margin-top: 0; }
</style>
</head>

<body>

<header class="zoo-header">
    <div class="container zoo-nav">
        <a href="homescreen.php" class="zoo-logo">🐾 Zoo Explorer</a>
        <nav class="zoo-menu d-none d-md-flex">
            <div class="menu-item"><a href="homescreen.php" style="color:#0b3d2e; font-weight:700;">Home</a></div>
            <div class="menu-item"><a href="Animal-display.php">Animals</a></div> 
            <div class="menu-item"><a href="activity.php">Activities</a></div>
            <div class="menu-item"><a href="zone.php">Zones</a></div>
            <div class="menu-item"><a href="zoo_social.php">Community</a></div>
            <div class="menu-item"><a href="about_us.php">About us</a></div>
        <div class="zoo-actions">
            <a href="ticket.php" class="buy-ticket d-none d-sm-block">BUY TICKETS</a>
            <div class="user-menu">
                <div class="user-avatar">
                    <img src="<?= $my_avatar ?>" alt="Avatar">
                </div>
                <div class="user-dropdown">
                    <div style="padding: 10px 20px; border-bottom:1px solid #eee; color:#0b3d2e; font-weight:bold;">Hello, <?= $my_name ?></div>
                    <a href="Profile_user.php">👤 Account info</a>
                    <a href="ticket_history.php">🎟 Ticket history</a>
                    <a href="logout.php">🚪 Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="about-hero">
    <div class="container">
        <h1>Về Zoo Explorer</h1>
        <p>Không chỉ là sở thú, chúng tôi là ngôi nhà của thiên nhiên hoang dã.</p>
    </div>
</section>

<section class="intro-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <img src="uploads/Screenshot 2026-01-02 180113.png" alt="Zoo Intro" class="intro-img" onerror="this.src='https://via.placeholder.com/600x400?text=Zoo+Image'">
            </div>
            <div class="col-md-6 pl-md-5 mt-4 mt-md-0">
                <h2 class="section-title">Câu Chuyện Của Chúng Tôi</h2>
                <p class="lead" style="color: #0b3d2e; font-weight: 500;">Được thành lập vào năm 2026 với sứ mệnh kết nối con người với thế giới tự nhiên.</p>
                <p>Zoo Explorer bắt đầu như một khu bảo tồn nhỏ dành cho các loài động vật bị đe dọa. Qua nhiều năm phát triển, chúng tôi đã trở thành một trong những công viên động vật hoang dã hàng đầu, nơi sinh sống của hơn 500 cá thể thuộc 100 loài khác nhau.</p>
                <p>Chúng tôi tin rằng, cách tốt nhất để bảo vệ thiên nhiên là giúp mọi người hiểu và yêu quý nó. Mỗi tấm vé bạn mua đều đóng góp trực tiếp vào quỹ bảo tồn và chăm sóc động vật của chúng tôi.</p>
            </div>
        </div>
    </div>
</section>

<section class="mission-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Sứ Mệnh & Giá Trị</h2>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="mission-card">
                    <div class="mission-icon"><i class="fas fa-hand-holding-heart"></i></div>
                    <h4>Bảo Tồn (Conservation)</h4>
                    <p>Chúng tôi cam kết bảo vệ các loài động vật có nguy cơ tuyệt chủng thông qua các chương trình nhân giống và tái thả về tự nhiên.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="mission-card">
                    <div class="mission-icon"><i class="fas fa-book-reader"></i></div>
                    <h4>Giáo Dục (Education)</h4>
                    <p>Truyền cảm hứng cho thế hệ trẻ về tầm quan trọng của đa dạng sinh học thông qua các hoạt động trải nghiệm thực tế.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="mission-card">
                    <div class="mission-icon"><i class="fas fa-paw"></i></div>
                    <h4>Phúc Lợi (Welfare)</h4>
                    <p>Đảm bảo môi trường sống tốt nhất, chế độ dinh dưỡng và chăm sóc y tế tiêu chuẩn quốc tế cho mọi con vật tại đây.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-number">100+</div>
                <div class="stat-label">Loài Động Vật</div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-number">50</div>
                <div class="stat-label">Hecta Diện Tích</div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-number">1M+</div>
                <div class="stat-label">Khách Tham Quan</div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Chăm Sóc Y Tế</div>
            </div>
        </div>
    </div>
</section>

<footer>
    <div class="container text-center">
        <p>© 2026 Zoo Explorer – Educational Project</p>
    </div>
</footer>

</body>
</html>