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
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Show Sư Tử Hùng Dũng | Zoo Explorer</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<style>
    /* ================= GLOBAL ================= */
    body{ margin:0; font-family:'Segoe UI',sans-serif; background:#fdf5e6; /* Nền vàng nhạt */ color: #333; }

    /* ================= HEADER (Đồng bộ) ================= */
    .zoo-header { background: #fff; padding: 15px 0; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 1000; }
    .zoo-nav { display: flex; align-items: center; justify-content: space-between; }
    .zoo-logo { font-size: 24px; font-weight: 800; color: #0b3d2e; text-decoration: none; }
    .zoo-logo:hover { color: #0b3d2e; text-decoration: none; }

    .zoo-menu { display: flex; gap: 30px; }
    .menu-item > a { text-decoration: none; color: #222; font-weight: 600; font-size: 16px; padding: 10px 0; display: inline-block; transition: 0.2s; }
    .menu-item > a:hover { color: #0b3d2e; }

    .zoo-actions { display: flex; align-items: center; gap: 20px; }
    .buy-ticket { background: #f4f91d; color: #0b3d2e; font-weight: 700; padding: 10px 25px; border-radius: 999px; text-decoration: none; transition: 0.25s ease; white-space: nowrap; }
    .buy-ticket:hover { background: #e6eb00; text-decoration: none; color: #0b3d2e; }

    /* USER AVATAR */
    .user-menu { position: relative; }
    .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: #0b3d2e; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; user-select: none; overflow: hidden; border: 2px solid #eee; }
    .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .user-dropdown { position: absolute; top: 52px; right: 0; width: 240px; background: #fff; border-radius: 14px; box-shadow: 0 15px 35px rgba(0,0,0,0.15); padding: 10px 0; opacity: 0; visibility: hidden; transform: translateY(10px); transition: 0.25s ease; z-index: 2000; }
    .user-menu:hover .user-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
    .user-dropdown a { display: block; padding: 12px 20px; color: #222; text-decoration: none; font-weight: 600; }
    .user-dropdown a:hover { background: #f4f6f4; color: #0b3d2e; }

    /* ================= CUSTOM STYLES CHO LION ================= */

    /* 1. HERO SECTION */
    .lion-hero {
        position: relative;
        height: 60vh;
        background: url('uploads/lion_bg_full.jpg') center/cover no-repeat;
        display: flex; align-items: flex-end; padding-bottom: 50px;
    }

    .hero-overlay {
        position: absolute; top:0; left:0; right:0; bottom:0;
        background: linear-gradient(to top, rgba(61, 43, 31, 0.9), transparent);
    }

    .hero-content {
        position: relative; z-index: 2; color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }

    .hero-title {
        font-size: clamp(36px, 6vw, 72px); font-weight: 900; margin: 0;
        text-transform: uppercase; letter-spacing: 2px; color: #f4f91d; 
    }

    .hero-subtitle {
        font-size: 20px; font-weight: 600; color: #ffd700; 
        margin-top: 10px; display: flex; gap: 20px; align-items: center;
    }

    /* 2. INFO BOX NỔI */
    .info-box-wrapper { margin-top: -40px; position: relative; z-index: 5; }
    .info-card {
        background: white; padding: 30px; border-radius: 15px;
        box-shadow: 0 10px 30px rgba(61, 43, 31, 0.15);
        display: flex; justify-content: space-around; align-items: center;
        border-bottom: 5px solid #d4a017; 
    }
    .info-item { text-align: center; }
    .info-item i { font-size: 28px; color: #d4a017; margin-bottom: 10px; }
    .info-item h4 { margin: 0; font-size: 14px; color: #777; text-transform: uppercase; }
    .info-item p { margin: 5px 0 0; font-size: 18px; font-weight: 800; color: #3d2b1f; }

    /* 3. NỘI DUNG CHÍNH */
    .main-content { padding: 60px 0; font-size: 17px; line-height: 1.8; }
    
    .highlight-text {
        font-size: 24px; font-weight: 300; color: #3d2b1f; 
        text-align: center; max-width: 800px; margin: 0 auto 50px; font-style: italic;
        border-left: 3px solid #d4a017; border-right: 3px solid #d4a017; padding: 0 20px;
    }

    .section-title { font-weight: 800; color: #d4a017; margin-bottom: 20px; text-transform: uppercase; }
    .check-icon { color: #d4a017; margin-right: 10px; }

    /* Gallery */
    .gallery-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 40px 0; }
    .gallery-img { width: 100%; height: 250px; object-fit: cover; border-radius: 10px; transition: transform 0.3s; border: 2px solid #d4a017; }
    .gallery-img:hover { transform: scale(1.03); box-shadow: 0 5px 15px rgba(61, 43, 31, 0.3); }

    /* CTA Section */
    .cta-section {
        background: #3d2b1f; 
        color: white; padding: 50px; text-align: center; border-radius: 20px; margin-top: 40px;
        background-image: url('uploads/pattern_paw.png'); 
    }

    .btn-ticket-large {
        background: #d4a017; color: #fff; 
        font-size: 20px; font-weight: 800; padding: 15px 50px;
        border-radius: 50px; display: inline-block; margin-top: 20px;
        text-decoration: none; box-shadow: 0 5px 0 #a67c00; transition: all 0.2s;
    }
    .btn-ticket-large:hover { transform: translateY(3px); box-shadow: 0 2px 0 #a67c00; color: #fff; text-decoration: none; }

    footer { background:#0b3d2e; color:white; padding:30px 0; margin-top: 0; }
</style>
</head>

<body>

<header class="zoo-header">
    <div class="container zoo-nav">
        <a href="homescreen.php" class="zoo-brand"><i class="fas fa-paw"></i> Zoo Explorer</a>
        <nav class="zoo-menu d-none d-md-flex">
            <div class="menu-item"><a href="homescreen.php">Home</a></div>
            <div class="menu-item"><a href="animals.php">Animals</a></div>
            <div class="menu-item"><a href="activity.php">Activities</a></div>
            <div class="menu-item"><a href="zone.php">Zones</a></div>
            <div class="menu-item"><a href="zoo_social.php">Community</a></div>
            <div class="menu-item"><a href="about_us.php">About us</a></div>
        </nav>
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

<div class="lion-hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="hero-subtitle">
            <span><i class="fas fa-paw"></i> BIG CATS ZONE</span>
            <span>|</span>
            <span><i class="fas fa-fire"></i> THRILLING SHOW</span>
        </div>
        <h1 class="hero-title">The Lion King<br>Live Performance</h1>
    </div>
</div>

<div class="container info-box-wrapper">
    <div class="info-card">
        <div class="info-item">
            <i class="far fa-clock"></i>
            <h4>Show Time</h4>
            <p>11:00 & 16:30</p>
        </div>
        <div class="info-item">
            <i class="fas fa-hourglass-half"></i>
            <h4>Duration</h4>
            <p>25 Minutes</p>
        </div>
        <div class="info-item">
            <i class="fas fa-map-marker-alt"></i>
            <h4>Location</h4>
            <p> BIG CATS ZONE (Zone 1)</p>
        </div>
        <div class="info-item">
            <i class="fas fa-ticket-alt"></i>
            <h4>Price</h4>
            <p>Free with Entry</p>
        </div>
    </div>
</div>

<div class="container main-content">
    
    <p class="highlight-text">
        "Cảm nhận sức mạnh rung chuyển mặt đất! Chứng kiến sự uy nghiêm của Chúa tể Sơn lâm trong một màn trình diễn đầy kịch tính và tôn vinh bản năng hoang dã."
    </p>

    <div class="row">
        <div class="col-md-6">
            <h3 class="section-title">Điểm Nhấn Của Show Diễn</h3>
            <p>Không chỉ là xiếc, đây là một màn trình diễn thể hiện sức mạnh và sự gắn kết giữa con người và loài thú dữ:</p>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 15px;"><i class="fas fa-check-circle check-icon"></i> Những cú nhảy ngoạn mục qua vòng lửa (được kiểm soát an toàn).</li>
                <li style="margin-bottom: 15px;"><i class="fas fa-check-circle check-icon"></i> Nghe tiếng gầm vang dội của sư tử đực trưởng thành ở cự ly gần.</li>
                <li style="margin-bottom: 15px;"><i class="fas fa-check-circle check-icon"></i> Tìm hiểu về tập tính săn mồi và bảo tồn loài sư tử trong tự nhiên.</li>
            </ul>
        </div>
        <div class="col-md-6">
            <div style="border-radius: 15px; overflow: hidden; box-shadow: 0 10px 20px rgba(61, 43, 31, 0.3); border: 3px solid #d4a017;">
                <img src="uploads/Screenshot 2026-01-02 172441.png" style="width: 100%; height: 300px; object-fit: cover;" alt="Lion Show Video" onerror="this.src='https://via.placeholder.com/600x400?text=Lion+Show+Video'">
            </div>
        </div>
    </div>

    <h3 class="section-title" style="margin-top: 50px;">Thư Viện Ảnh Hoang Dã</h3>
    <div class="gallery-grid">
        <img src="uploads/Screenshot 2026-01-02 172150.png" class="gallery-img" alt="Lion roaring" onerror="this.src='https://via.placeholder.com/400x300?text=Lion+1'">
        <img src="uploads/Screenshot 2026-01-02 172559.png" class="gallery-img" alt="Lion jumping fire" onerror="this.src='https://via.placeholder.com/400x300?text=Lion+2'">
        <img src="uploads/Screenshot 2026-01-02 172712.jpg" class="gallery-img" alt="Trainer and Lion" onerror="this.src='https://via.placeholder.com/400x300?text=Lion+3'">
    </div>

    <div class="cta-section">
        <h2 style="margin: 0 0 10px 0; font-weight: 800; color: #f4f91d;">Chỗ Ngồi Đẹp Nhất Đang Chờ Bạn!</h2>
        <p>Khán đài thường kín chỗ rất nhanh. Hãy đến sớm hoặc đặt vé trước để có góc nhìn tốt nhất.</p>
        <a href="ticket.php" class="btn-ticket-large">ĐẶT VÉ NGAY BÂY GIỜ</a>
    </div>

</div>

<footer>
    <div class="text-center">
        <p>© 2026 Zoo Explorer – Educational Project</p>
    </div>
</footer>

</body>
</html>